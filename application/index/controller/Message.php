<?php
namespace app\index\controller;
header('Content-Type:text/html;Charset=utf-8');
use think\Controller;
use think\Request;
use think\Url;


class Message extends Controller
{
	public function index(){
		$arr = array(
		    "id"=>$_GET["id"],
		    "name"=>$_GET["name"],
		    "phone"=>$_GET["phone"]
		);
		echo $_GET['callback']."(".json_encode($arr).")";//此处的callback需要和客户端ajax请求中的jsonp属性保持一致。
	}
// 	public function index(){
// 		$url="index/index";
// 		return<<<EOT
// 		<script type="text/javascript" src="http://cdn.bootcss.com/jquery/2.1.1/jquery.min.js"></script>
// 		<form>
// 			<input type="text" name="name" placeholder="姓名">
// 			<input type="text" name="cellphone" placeholder="手机号">
// 			<input type="submit" onsubmit="return test()" value="提交">
// 		</form>
// 		<script>
			
// 		</script>
// EOT;
	// }
}