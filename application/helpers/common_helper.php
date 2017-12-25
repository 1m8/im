<?php
// +----------------------------------------------------------------------
// | description: 公用类
// +----------------------------------------------------------------------
// | author: lidl
// +----------------------------------------------------------------------
// | date: 2017年1月9日
// +----------------------------------------------------------------------
// | Author: lidl <lidalin.se@gmail.com><http://1m85.com>
// +----------------------------------------------------------------------
defined('BASEPATH') OR exit('No direct script access allowed');


if ( ! function_exists('ajaxReturn')){

	function ajaxReturn($code, $info){

		$data = ['code' => $code, 'info'	=> $info];
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit();

	}
}




?>