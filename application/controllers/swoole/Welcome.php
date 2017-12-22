<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	
	public function index()
	{
		
		$server = new swoole_websocket_server("0.0.0.0", 9501);
		
		$server->on('open', function (swoole_websocket_server $server, $request) {
			echo "server: handshake success with fd{$request->fd}\n";
			$server->push($request->fd, "连接成功......");
			
		});
			
		$server->on('message', function (swoole_websocket_server $server, $frame) {
			echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
			print_r($frame);
			$server->push($frame->fd, "this is server");
		});
			
		$server->on('close', function ($ser, $fd) {
			echo "client {$fd} closed\n";
		});
			
		$server->start();
	}
	
	
	/**
	 * route
	 * 
	 */
	private function route()
	{
		
			
		
	}
	
	/**
	 * login
	 * 
	 * @param 
	 * 
	 */
	private function login($fd)
	{
			
			
		
		
	}
	
	
	
}
