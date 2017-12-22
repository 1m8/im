<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	
	public function index()
	{
		
		/**
		 * 登录 GET
		 */
		if($this->input->method() == 'GET')
		{
			$this->load->view('font/login');
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
		if($this->input->method() == 'POST')
		{
			
			$user_name	= $this->input->post('userName');
			$password	= $this->input->post('password');
			
			$query 	= $this->db->where('user_name', $user_name)->get('users');	
			$row 	= $query->row_array();
			
			if(empty($row))
			{
				ajaxReturn(1, '用户不存在');
			}
			
			
			
			
			
		}
		
	
	}
	
	
	
	
	
	
}
