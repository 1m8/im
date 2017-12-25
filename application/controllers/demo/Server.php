<?php
// +----------------------------------------------------------------------
// | description: Server
// +----------------------------------------------------------------------
// | author: lidl
// +----------------------------------------------------------------------
// | date: 2017年12月21日
// +----------------------------------------------------------------------
// | Author: lidl <lidalin.se@gmail.com><http://1m85.com>
// +----------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

class Server extends CI_Controller {

	
	/**
	 * 创建一个异步Server对象。
	 * 
	 * $serv = new swoole_server(string $host, int $port = 0, int $mode = SWOOLE_PROCESS, int $sock_type = SWOOLE_SOCK_TCP);
	 * 
	 * $host  服务监听的IP地址，'0.0.0.0' 表示监听所有IP
	 * $port  监听的端口 端口小于1024需root权限
	 * $mode  运行的模式，swoole提供了3种运行模式，默认为SWOOLE_PROCESS多进程模式 
	 * $sock_type 指定Socket的类型，支持TCP（SWOOLE_SOCK_TCP）、UDP（SWOOLE_SOCK_UDP）、TCP6、UDP6、UnixSocket Stream/Dgram 6种
	 * 
	 * 
	 */
	public function tcp()
	{
		
		$serv 	= new swoole_server('0,0,0,0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP );				
		
		$serv->on('connect', function ($serv, $fd){
			echo "Client:Connect.fd:$fd\n";
		});
		
		$serv->on('receive', function ($serv, $fd, $from_id, $data) {
			$serv->send($fd, 'Swoole: '.$data);
		});
		
		$serv->on('close', function ($serv, $fd) {
			echo "Client: Close.\n";
		});
		
		$serv->start();
		
	}
	
	
	/**
	 * 
	 * 创建UDP服务器
	 * 
	 * UDP 协议无需3次握手，效率更快，稳定性略差。只有receive 回调函数
	 * 
	 * receive(swoole_server $server, int $fd, int $reactor_id, string $data)
	 * 
	 * 
	 * 
	 */
	public function udp()
	{
		
		$serv	= new swoole_server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
		
		$serv->on('packet', function($serv, $data, $client_info){
			
			echo 'Udp receive:'.$data;	
			$serv->sendto($client_info['address'], $client_info['port'] ,'server receive: '.$data, $client_info['server_socket']);
			
		});
		
		
		$serv->start();
		
		
	}
	
	
	
	/**
	 *  HttpServer 创建http服务器
	 *  
	 *  httpServer 继承自 swoole_server
	 *  
	 *  注册事件回调函数，与swoole_server->on相同。swoole_http_server->on的不同之处是：
	 *  
	 *  swoole_http_server->on不接受onConnect/onReceive回调
	 *  设置swoole_http_server->on 额外接受1种新的事件类型onRequest
	 *  
	 * 
	 */
	public function http()
	{
		$serv 	= new swoole_http_server("0.0.0.0", 9501);
		
		$serv->on('request', function ($request, $response) {
			$response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
		});
		
		$serv->start();
		
	}
	
	
	/**
	 * 
	 * webSocket 创建websocket服务器
	 * 
	 * 
	 * 
	 */
	public function websocket()
	{
		
		$serv = new swoole_websocket_server("0.0.0.0", 9501);
		
		$serv->on('open', function ($serv, $request) {
			echo "server: handshake success with fd{$request->fd}\n";
			$serv->push($request->fd, "open success");
		});
			
		$serv->on('message', function ($serv, $frame) {
			echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
			$serv->push($frame->fd, "this is server:".$frame->data);
		});
			
		$serv->on('close', function ($serv, $fd) {
			echo "client {$fd} closed\n";
		});
		
		$serv->start();
		
		
	}
	
	
	
	/**
	 * 定时器
	 * 
	 * swoole_timer_tick
	 * 
	 * int swoole_timer_tick(int $ms, callable $callback, mixed $user_param);
	 * 
	 */
	public function timerTick()
	{
		$user_param = 'param';
		
		swoole_timer_tick(2000, function($timerid, $user_param){

			echo time()."\n";
			
		} , $user_param);
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
}
