<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Im extends CI_Controller {

	
	/**
	 *  Index
	 *  
	 *  {"cmd":"function", "msg":"msg"}
	 * 
	 */
	public function index()
	{

		$serv = new swoole_websocket_server("0.0.0.0", 9501);
		
		$serv->on('open', function ($serv, $request) {
					
			echo "server: handshake success with fd{$request->fd}\n";
			//获取所有在线用户
			$query 	= $this->db->get('msg');
			$rows	= $query->result_array();
			
			foreach ($rows as $v)
			{
				$serv->push($request->fd, json_encode(['code'=>21,'info'=>['id'=>$v['id'], 'name'=>$v['user_name']]]));
			}
			
		});
		
			
		$serv->on('message', function ($serv, $frame) {
			
			echo "receive from {$frame->fd}:{$frame->data}, opcode:{$frame->opcode},fin:{$frame->finish}\n";
					
			$obj	= 	json_decode($frame->data);
						
			switch ($obj->cmd){
				
				case 'login':
					$this->login($obj->msg, $obj->token, $frame->fd, $serv);
					break;
					
				case 'msg':
					$this->msg($obj->msg, $frame->fd, $serv);
					break;
				
			}
			
			
			
		});
			
		$serv->on('close', function ($serv, $fd) {
			
			$this->db->delete('msg', ['fid'=>$fd]);
			//通知所有人
			$query 	= $this->db->get('msg');
			$rows	= $query->result_array();
			
			//count
			$count = $this->db->count_all('msg');
			
			//下线通知
			foreach ($rows as $v)
			{
				$serv->push($v['fid'], json_encode(['code'=>41,'info'=>['fid'=>$fd, 'count'=>$count]]));
			}
			
			
		});
			
		$serv->start();	
	}
	
	
	
	/**
	 * 
	 * 登录
	 * @param swoole_websocket_frame obj $frame  用户数据帧信息
	 * 
	 */
	private function login($msg, $token, $fid, $serv)
	{
		//根据token 查找用户
		$query = $this->db->where('token', $token)->get('users');
		$row	= $query->row_array();
		
		if(empty($row))
		{
			$serv->push($fid, json_encode(['code'=>1, "info"=>"登录失败"]));	
			exit;
		}
		
		//通知所有人
		$query 	= $this->db->get('msg');
		$rows	= $query->result_array();
		
		//count
		$count = $this->db->count_all('msg');
		
		foreach ($rows as $v)
		{
			$serv->push($v['fid'], json_encode(['code'=>21,'info'=>['id'=>$row['id'], 'name'=>$row['user_name'], 'fid'=>$fid, 'count'=>++$count]]));	
			
		}
		
		
		//写入聊天室
		$this->db->insert('msg', ['fid'=>$fid, 'user_id'=>$row['id'], 'user_name'=>$row['user_name']]);
	}
	
	
	
	/**
	 * 
	 * 聊天信息 推送给所有人
	 * 
	 */
	private function msg($msg, $fid, $serv)
	{
		$query  = $this->db->where('fid', $fid)->get('msg');
		$row	= $query->row_array();
		
		$query 	= $this->db->where('fid !=', $fid)->get('msg');
		$rows	= $query->result_array();
		
		foreach ($rows as $v)
		{
			$serv->push($v['fid'], json_encode(['code'=>31,'info'=>['send_id'=>$row['id'], 'send_name'=>$row['user_name'], 'msg'=>$msg]]));
		}
	
	}

	
	
	
	
	
	
}
