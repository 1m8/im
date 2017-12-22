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
if(!function_exists("responseJson")){
    function responseJson(array $data,$jnn = JSON_UNESCAPED_UNICODE){
            if(version_compare(PHP_VERSION,'5.4') >= 0){
                echo json_encode($data,$jnn);
            }else{
                echo json_encode($data);
            }
            exit; 
    }      
}
if ( ! function_exists('ajaxReturn')){

	function ajaxReturn($code, $info){

		$data = ['code' => $code, 'info'	=> $info];
		echo json_encode($data);
		exit();

	}
}


/**
 *
 * 获取wechat
 */
if ( ! function_exists('vailWechat')){

	/*
	 * curl
	 */
	function vailWechat($wechat, $bool=false){


		$CI 		= &get_instance();
		//判断用户是否登录
		//openid不存在
		if(empty($wechat)){
		
			if($bool)
			{
				ajaxReturn(1, '扫码超时，请重新扫码');
		
			}else{
				$CI->load->view('/common/error', ['msg' => '扫码超时，请重新扫码']); //错误提示
				$CI->output->_display();
				exit;
		
			}
		
		}
		
		return  $wechat;

	}
}



if ( ! function_exists('curl')){

	/*
	 * curl
	 */
	function curl($url, $data=''){

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		$re = curl_exec ( $ch );

		curl_close ( $ch );

		return $re;

	}
}

if ( ! function_exists('getAccessToken')){

	/*
	 * curl
	 * @return access_token
	 */
	function getAccessToken($wechat){

		$CI 		= &get_instance();

		//获取row
		$query 		= $CI->db->get_where('access_token', ['appid' => $wechat['appid']]);
		$row   		= $query->first_row();

		if((time()-$row->time) >= 0){
			//获取token
			$uri = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wechat['appid'].'&secret='.$wechat['appsecret'];

			$re  = curl($uri);

			$arr = json_decode($re, true);
			
			if(!empty($arr['errcode']))
			{
				ajaxReturn(1, 'accesstoken获取失败');
			}

			$access_token 	= $arr['access_token'];
			
			
			$data = array(
					'access_token'	=> $access_token,
					'time'			=> time(),
			);
				
			$result = $CI->db->update('access_token', $data, ['appid'=>$wechat['appid']]);

		}else{
			$access_token = $row->access_token;
		}

		return $access_token;
	}

}

if ( ! function_exists('getCardTicket')){

	/*
	 * curl
	 * @return access_token
	 */
	function getCardTicket($wechat){

		$CI 		= &get_instance();
		
		//获取row
		$query 		= $CI->db->get_where('access_token', ['appid' => $wechat['appid']]);
		$row   		= $query->first_row();
		
		if((time()-$row->card_ticket_time) > 3500){
		
			$access_token = getAccessToken($wechat);
			//获取token
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=wx_card";
			$re  = curl($url);
		
			$arr = json_decode($re, true);
		
			$ticket 	= $arr['ticket'];
		
			$data = array(
					'card_ticket'				=> $ticket,
					'card_ticket_time'			=> time(),
			);
		
			$result = $CI->db->update('access_token', $data, ['appid'=>$wechat['appid']]);
		
		}else{
			$ticket = $row->card_ticket;
		}
		
		return $ticket;
	}

}

if ( ! function_exists('getJsApiTicket')){

	/*
	 * curl
	 * @return access_token
	 */
	function getJsApiTicket($wechat){

		$CI 		= &get_instance();

		//获取row
		$query 		= $CI->db->get_where('access_token', ['appid' => $wechat['appid']]);
		$row   		= $query->first_row();
		

		if((time()-$row->jsapi_ticket_time) > 3500){
				
			$access_token = getAccessToken($wechat);
			//获取token
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$access_token";
			$re  = curl($url);

			$arr = json_decode($re, true);
			
			$ticket 	= $arr['ticket'];

			$data = array(
					'jsapi_ticket'				=> $ticket,
					'jsapi_ticket_time'			=> time(),
			);

			$result = $CI->db->update('access_token', $data, ['appid'=>$wechat['appid']]);

		}else{
			$ticket = $row->jsapi_ticket;
		}

		return $ticket;
	}

}


if ( ! function_exists('getSignPackage')){

	/*
	 * curl
	 * @return access_token
	 */
	function getSignPackage($wechat){

		$jsapiTicket = getJsApiTicket($wechat);

		// 注意 URL 一定要动态获取，不能 hardcode.
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$timestamp 	= time();
		$nonceStr 	= randStr(16);

		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

		$signature = sha1($string);

		$signPackage = array(
				"appId"     => $wechat['appid'],
				"nonceStr"  => $nonceStr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
				"rawString" => $string
		);
		return $signPackage;
	}

}


if ( ! function_exists('wxQrCode')){

	/*
	 * curl
	 */
	function wxQrCode($id, $wechat){

		$access_token = getAccessToken($wechat);
		//获取二维码
		$uri 	= 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
		//$data	= '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$id.'"}}}';
		$data = '{"expire_seconds": 2592000, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$id.'}}}';
		$re = curl($uri, $data);
		
		$arr 	= json_decode($re, true);

		return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$arr['ticket'];

	}
}




//随机字符串
if ( ! function_exists('randStr')){

	/*
	 * curl
	 */
	function randStr($length){

		$arr = ['a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9'];
		shuffle($arr);
		$str = '';

		for($i=1; $i<=$length; $i++){
			$str .= $arr[$i];
		}

		return $str;
	}
}



//check
if ( ! function_exists('getCase')){

	/*
	 * checkToken
	 */
	function getCase($case_id){

		$CI 	= &get_instance();

		$query 			= $CI->db->get_where('case', ['id'=> $case_id]);
		$rowCase		= $query->first_row();

		if(empty($rowCase)){
			$data = ['msg'=>'模版参数错误'];
			$CI->load->view('error_common', $data);
			$CI->output->_display();
			exit;
		}

		return $rowCase;
	}


}


//get template
if ( ! function_exists('getTemplate')){

	/*
	 * checkToken
	 */
	function getTemplate($id){

		$CI 	= &get_instance();

		$query 			= $CI->db->get_where('template', ['id'=> $id]);
		$rowTemplate	= $query->first_row();

		if(empty($rowTemplate)){
			$data = ['msg'=>'模版信息错误'];
			$CI->load->view('error_common', $data);
			$CI->output->_display();
			exit;
		}

		return $rowTemplate;
	}


}



/**
 *
 * 微信信息处理-回复普通文本信息
 *
 *
 */
if ( ! function_exists('wxResponseText')){

	function wxResponseText($toUser, $msg, $wechat){

		$xml = '<xml>
<ToUserName><![CDATA['.$toUser.']]></ToUserName>
<FromUserName><![CDATA['.$wechat['original_id'].']]></FromUserName>
<CreateTime>'.time().'</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA['.$msg.']]></Content>
</xml>';

		return $xml;
	}

}

/*
 *
 * array to xml
 *
 *
 *
 */
if ( ! function_exists('arrayToXml')){
	
	function arrayToXml($arr){
		$xml = "<xml>";
		foreach ($arr as $key=>$val){
			if(is_array($val)){
				$xml.="<".$key.">".arrayToXml($val)."</".$key.">";
			}else{
				$xml.="<".$key.">".$val."</".$key.">";
			}
		}
		$xml.="</xml>";
		return $xml;
	}
	
}

if ( ! function_exists('wxResponseImgText')){
	
	function wxResponseImgText($toUser, $fromUser, $title, $imgurl, $url, $des=''){
		
		$xml =
		'<xml>
<ToUserName><![CDATA['.$toUser.']]></ToUserName>
<FromUserName><![CDATA['.$fromUser.']]></FromUserName>
<CreateTime>'.time().'</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA['.$title.']]></Title>
<Description><![CDATA['.$des.']]></Description>
<PicUrl><![CDATA['.$imgurl.']]></PicUrl>
<Url><![CDATA['.$url.']]></Url>
</item>
</Articles>
</xml>';
		
		return $xml;
	}
	
}

/**
 * paycurl
 * 
 * 发放curl
 */
if ( ! function_exists('payCurl')){
	
	function payCurl($url, $path, $xml){
		
		$apiclient_cert = dirname(getcwd()).'/cert/'.$path.'/apiclient_cert.pem';
		$apiclient_key	= dirname(getcwd()).'/cert/'.$path.'/apiclient_key.pem';
		$rootca			= dirname(getcwd()).'/cert/'.$path.'/rootca.pem';
		
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT,$apiclient_cert);
		curl_setopt($ch,CURLOPT_CAINFO,$rootca);
		curl_setopt($ch,CURLOPT_SSLKEY,$apiclient_key);
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $xml );
		$re = curl_exec ( $ch );
		
		curl_close ( $ch );
		
		return $re;
		
	}
	
}



/**
 * 企业付款推送
 *
 *
 */
if ( ! function_exists('sendPackNosbr')){
	
	function sendPackNosbr($openid, $amount, $title, $wechat){
		
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		
		$data = [];
		$data['mch_appid'] 	= $wechat['appid'];
		$data['mchid']		= $wechat['mch_id'];
		$data['nonce_str']	= randStr(31);
		$data['partner_trade_no'] = rand(1111111111,99999999999);
		$data['openid']			  = $openid;
		$data['check_name']		  = 'NO_CHECK';
		$data['amount']			  = $amount;
		$data['desc']			  = $title;
		$data['spbill_create_ip'] = '115.159.120.33';
		
		//排序
		ksort($data);
		//生成sign
		$str 	= urldecode(http_build_query($data)).'&key='.$wechat['api_key'];
		$sign 	= strtoupper(md5($str));
		
		$data['sign'] = $sign;
		
		$xml = arrayToXml($data);
	
		return payCurl($url, $wechat['cert_path'], $xml);
		
	}
	
}



/**
 * 普通红包
 */
if ( ! function_exists('commonWxPack')){
	

	function commonWxPack($act_name, $send_name, $re_openid, $total_amount, $wishing, $remark,$mch_billno, $wechat){
		
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
		
		$data['act_name'] 		= $act_name;
		$data['client_ip']		= '115.159.120.33';
		$data['mch_billno']		= $mch_billno;
		$data['mch_id']			= $wechat['mch_id'];
		$data['nonce_str']		= randStr(32);
		$data['re_openid']		= $re_openid;
		$data['remark']			= $remark;
		$data['scene_id']		= 'PRODUCT_2';
		$data['send_name']		= $send_name;
		$data['total_amount']	= $total_amount;
		$data['total_num']		= 1;
		$data['wishing']		= $wishing;
		$data['wxappid']		= $wechat['appid'];
		
		//排序
		ksort($data);
		//生成sign
		$str 	= urldecode(http_build_query($data)).'&key='.$wechat['api_key'];
		
		$sign 	= strtoupper(md5($str));
		$data['sign'] = $sign;
		
		$xml = arrayToXml($data);
		
		return payCurl($url, $wechat['cert_path'], $xml);

		
	}
}


//分裂红包
if ( ! function_exists('fissionWxPack')){
	
	/*
	 * curl
	 */
	function fissionWxPack($act_name, $send_name, $re_openid, $total_amount,$total_num, $wishing, $remark,$mch_billno, $wechat){
		
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack';
		
		$data['act_name'] 		= $act_name;
		$data['amt_type']		= 'ALL_RAND';
		$data['client_ip']		= '115.159.120.33';
		$data['mch_billno']		= $mch_billno;
		$data['mch_id']			= $wechat['mch_id'];
		$data['nonce_str']		= randStr(32);
		$data['re_openid']		= $re_openid;
		$data['remark']			= $remark;
		$data['send_name']		= $send_name;
		$data['total_amount']	= $total_amount;
		$data['total_num']		= $total_num;
		$data['wishing']		= $wishing;
		$data['wxappid']		= $wechat['appid'];
		
		//排序
		ksort($data);
		//生成sign
		$str 	= urldecode(http_build_query($data)).'&key='.$wechat['api_key'];
		$sign 	= strtoupper(md5($str));
		$data['sign'] = $sign;
		
		$xml = arrayToXml($data);
		
		elog($xml,'tt','tt');
		
		return payCurl($url, $wechat['cert_path'], $xml);
		
	}
}


/*
 *
 * array to xml
 *
 * paylog
 *
 */
if ( ! function_exists('elog')){
	
	function elog($con,$title, $level= 'NOTICE'){
		//日志按天生成
		$path = dirname(getcwd()).'/log/'.'paylog_'.date('Ymd').'.log';
		
		file_put_contents($path, $level.'【'.date('Y-m-d H:i:s').'】'.PHP_EOL, FILE_APPEND);
		file_put_contents($path, $title.PHP_EOL, FILE_APPEND);
		file_put_contents($path, $con.PHP_EOL.PHP_EOL, FILE_APPEND);
	}
	
}

/**
 * getinfo
 */
if ( ! function_exists('getInfo')){
	
	function getInfo($openid, $wechat){
		
		$access_token = getAccessToken($wechat);
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		//回填
		$re = curl($url);
		$arr = json_decode($re, true);
		return $arr;
	}
	
}

?>