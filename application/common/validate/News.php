<?php
namespace app\common\validate;
use think\Validate;

class News extends Validate{
	protected $rule=[
		['Sid','require|number',"必须填写项目分类ID|项目分类id必须数字！"],
		['name','require|chs',"姓名必须|姓名为中文"],

	];

}
