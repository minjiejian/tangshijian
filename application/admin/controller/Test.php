<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Url;
use think\Session;
use think\Validate;
class Test extends Admin
{
	public function index(){
		$rule = [
		    'age'   => 'number|between:1,120',
		    'email' => 'email',
		];

		$msg = [
		    'age.number'   => '年龄必须是数字',
		    'age.between'  => '年龄只能在1-120之间',
		    'email'        => '邮箱格式错误',
		];
		$data = [
				    'age'   => 11,
				    'email' => 'thinkphpqq.com',
		];
		$validate=new Validate($rule,$msg);
		$result=$validate->check($data);
		echo'<pre>';
		if($result){
			$test=new \app\common\model\Test;
			$t=$test->save($data);
			print_R($t);exit;
		}
		echo'<pre>';
		print_R($validate);
		echo $validate->geterror();
		// $date=['email'=>123,'age'=>25,'addr'=>'guangz'];
	}
}