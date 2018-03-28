<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Url;
use think\DB;
use think\Validate;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
    public function msg(){
    	if(request()->isAjax()){
		    	$news= new \app\common\model\News;
	    		$msg	=array();
	    		$msg['Sid']    =Request::instance()->param('id');
	    		$msg['Sortid'] =DB::table("tp_news")->max('Sortid')+1;
	    		$msg['name']   =input('request.name');
	    		$msg['phone']  =input('request.phone');
	    		$msg['contact']=input('request.contact');
	    	    $msg['ADDR']   =input('request.addr');
	    		$msg['Content']=input('request.content');
	    		$msg['Dtime']  = date('y-m-d H:i:s',time());

		    	if($news->data($msg)){
		    		$news->save();
		    		echo $msg['Sid'];
		    		
		    	}else{
		    		echo 2;
					// $this->error($news->getError());
		    	}
	    }else{
			
	    	

	    }
	}

	public function msgform2(){
		$news= new \app\common\model\News;
		$backurl=$_GET["backurl"]?$_GET["backurl"]:"";
		$from=$_GET["from"]?$_GET["from"]:"";
		$validate=validate('News');
		$data=["Sid"=>$_GET["sid"],"name"=>$_GET["name"]];

		if(!$validate->check($data)){
			echo"1";
		};

		$arr = array(
		    "Sid"=>$_GET["sid"],
		    "name"=>$_GET["name"],
		    "phone"=>$_GET["phone"],
		    "contact"=>$_GET["contact"],
		    "ADDR"=>$_GET["addr"],
		    "Content"=>$_GET["content"],
		    "keyword"=>$_GET["keyw"],
		    "referrerurl"=>$backurl,
		    "from"=>$from,
		    'Dtime'=>date('y-m-d H:i:s',time()),
		    'Sortid'=>DB::table("tp_news")->max('Sortid')+1
		);
		if($news->data($arr)){
		    		$news->save();
		    		// echo $_GET["callback"]."(".json_encode($arr).")";
		    		echo $_GET["callback"]."(".json_encode(array()).")";
		    	}else{
					$this->error($news->getError());
		    	}
	}
}
