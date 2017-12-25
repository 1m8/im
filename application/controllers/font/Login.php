<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller{

	
	public function index()
	{
		
		
		/**
		 * 登录 GET
		 */
		if($this->input->method() == 'get')
		{
			
			$count = $this->db->count_all('msg');
			
			$this->load->view('font/header');
			$this->load->view('font/login',['count'=>$count]);
			$this->load->view('font/footer');
		}
		
		
		/**
		 *  用户登录  POST
		 *  
		 *  @param string userName 用户名
		 *  @param string password 密码
		 *  
		 *  @return string json 返回json格式token
		 *  
		 */
		if($this->input->method() == 'post')
		{
			
			$user_name	= $this->input->post('name');

			$token 	= md5(md5($user_name).rand());
			$this->db->insert('users', ['user_name'=>$user_name, 'token'=>$token]);
			
			$count = $this->db->count_all('msg');
			
			ajaxReturn(11, ['token'=>$token, 'count'=>++$count]);
			
		}
		
	
	}
	
	
	
	
	
	
}
