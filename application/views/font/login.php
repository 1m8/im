<style>
.border{border:1px solid #E3E3E3;}
.h100{height:100%;}
.h80{height:80%;}
.pt5{padding-top:5%;}
.title{border-bottom:1px solid #E3E3E3;text-align:center;line-height:45px;background:#585858;color:#FFF;}
.list{line-height:40px;border-bottom:1px solid #E3E3E3;padding-left:20px;}
.con{position:relative;}
.send{position:absolute;left: 0px;bottom: 0px;}
.msg{height:90%;padding:10px;overflow-y:scroll;}
.fr{text-align:right;padding:10px 20px 10px 0;}
.fl{text-align:left;padding:10px 20px 10px 0;}
.mbox{line-height:38px;line-height:40px;}
strong{padding:15px;background:#E3E3E3;border-radius:5px;}
</style>

<body class="h100">

<div class="layui-container pt5 h100">  
  	<div class="layui-row h100">
    	<div class="layui-col-lg3 h80 border" id="user">
      		<h3 class="title">程序员之家 ( 在线:<span id="count"><?php echo $count;?></span>人 )</h3>

    	</div>

    	<div class="layui-col-lg9 h100" style="padding-left:30px;">
      		<div class="border h80 con">
      			<div class="msg">

      			</div>
      			<div class="send" style="width: 100%;">
      		 		<div class="layui-col-lg10">
	      		 		<div class="layui-input-block" style="margin-left:0px;">
				      		<input type="text" name="title" required  lay-verify="required" id="mm" placeholder="请输入" autocomplete="off" class="layui-input">
				   	 	</div>  	
			   	 	</div>		
			   	 	<div class="layui-col-lg2">
			   	 		<div class="layui-input-block" style="margin-left:0px;">
			   	 			<button class="layui-btn" lay-submit lay-filter="formDemo" style="width:100%;" id="send" onkeydown="EnterPress()">发送</button>
			   	 		</div>
			   	 	</div>	
      			</div>
      		</div>

    	</div>
	</div>


	

</div>

</body>

<script>
var token 	= null;
var ws 		= new WebSocket("ws://182.92.184.167:9501");

ws.onopen = function(evt) { 
	  console.log("Connection open ...");
	  
};
	
ws.onmessage = function(evt) {
  	console.log( "Received Message: " + evt.data);
	//上线
	res = $.parseJSON(evt.data);
	if(res.code == 21)
	{
		$('#user').append('<p class="list fid'+res.info.fid+'">'+res.info.name+'</p>');
		$("#count").html(res.info.count);
	}

	if(res.code == 31)
	{
		$('.msg').append('<h3 class="fl"><span class="mbox">'+res.info.send_name+'：<strong>'+res.info.msg+'</strong></span></h3>');
	}

	//下线
	if(res.code == 41)
	{
		$(".fid"+res.info.fid).remove();	
		$("#count").html(res.info.count);
	}

  	
};

ws.onclose = function(evt) {
	 console.log("Connection closed.");
}; 

ws.onerror = function(evt) {

	console.log(evt);
}

//登录
function login(value,index)
{
	if(value == '' || value == 'null')
	{
		layer.msg("昵称不能为空！");
		return;
	}

	$.post('/index.php/font/login/index',{name:value},function(response){
	  	console.log(response);
	  	token = response.info.token;
	  	
	  	if(response.code == 11)
	  	{
	  		var mes = JSON.stringify({"cmd":"login", "token":token});
	  		$('#user').append('<p class="list">'+value+'</p>');
	  		$("#count").html(response.info.count);
	  		ws.send(mes);		
	  			
	  	}else{

	  		layer.msg("登录失败！");	
	  	}



	},'json')

	layer.close(index);
	
}

function send(){
	if(token == null || token == '')
	{
		layer.prompt({
		  formType: 0,
		  value: '',
		  title: '请输入昵称',
		  area: ['200px', '50px'] //自定义文本域宽高
		}, function(value, index, elem){
		  login(value,index);
		  layer.close(index);
		});
		return;
	}

	var mm = $("#mm").val();
	$('.msg').append('<h3 class="fr"><span class="mbox"><strong>'+mm+'</strong>：我</span></h3>')
	var mes = JSON.stringify({"cmd":"msg", "msg":mm});
	ws.send(mes);	
	var mm = $("#mm").val('');
	
}

$(document).keypress(function(e) {  
    // 回车键事件  
       if(e.which == 13) {  
   			send();
       }  
});


$("#send").click(function(){

	send();
		

})


	

</script>