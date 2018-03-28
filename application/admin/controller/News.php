<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Url;
use think\Session;
use think\Validate;

class News extends Admin
{
	public function index(){
		$this->error('页面不存在！');
	}
	public function classindex() {
		parent::userauth2(45);
		$newsclass = new \app\common\model\Newsclass;
		$user = new \app\common\model\User;
		$where = array();
		$lists  = $newsclass->where($where)->paginate();
		$volist = $lists->toArray();
		foreach($volist['data'] as $k=>$v){
		    $volist['data'][$k]['Username'] = $user->where("ID=".$v['Uid'])->value('Username');
		}
		$this->assign('volist',$volist);			
		$this->assign('lists',$lists);
		return $this->fetch();
	}

	//图片上传
	public function upload(){
		$file = request()->file('image');
		if($file){
	        $info = $file->move(ROOT_PATH .'public'.DS.'static' . DS . 'image'. DS .'upload');
	        echo'<pre>';
	        print_R($info);
	        exit;
	        if($info){
	            // 成功上传后 获取上传信息
	            // 输出 jpg
	            echo $info->getExtension();
	            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
	            echo $info->getSaveName();
	            // 输出 42a79759f284b767dfcb2a0197904287.jpg
	            echo $info->getFilename(); 
	        }else{
	            // 上传失败获取错误信息
	            echo $file->getError();
	        }
 	   }
	}


	//添加分类
	public function classadd() {
		parent::win_userauth(46);
		return $this->fetch();
	}
	//添加分类处理
	public function classadd_do() {
		parent::userauth(46);
		if (request()->isAjax()) {
			$data = array();
			$data['ClassName']   = input('post.classname');
			$data['Description'] = input('post.description');
			$newsclass = new \app\common\model\Newsclass;
			//自动创建并验证数据
			if ($newsclass->save($data)){
				parent::operating(request()->path(),0,'新增成功');
				//在创建项目类别时，自动往competence权限表插入数据
				$c=array();
				$c['Sid']         = 91;
				$c['Cname']       = $data['ClassName'];
				// $c['Description'] = input('post.description');
				$c['Status']      = 0;
				$co= new \app\common\model\Competence;
				if($co->data($c)){
					$co->save();
				}
				return array('s'=>'ok');
			}else {
				parent::operating(request()->path(),1,'新增失败:'.$newsclass->getError());
				return array('s'=>$newsclass->getError());
			}
		}else {
			parent::operating(request()->path(),1,'非法请求');
			return array('s'=>'非法请求');
		}
	}
	//修改分类
	public function classedit() {
		parent::win_userauth(47);
		$id = input('get.id');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(request()->path(),1,'参数错误');
			$this->assign('content','参数ID类型错误，请关闭本窗口');
			return $this->fetch('Public/err');
		}
		$id=intval($id);
		$newsclass= new \app\common\model\Newsclass;
		if ($result=$newsclass->where("ID=$id")->find()) {
			$this->assign('result',$result);
			return $this->fetch();
		}else {
			parent::operating(request()->path(),1,'数据不存在');
			$this->assign('content','没有找到相关数据，请关闭本窗口');
			return $this->fetch('Public/err');
		}
	}
	//修改分类处理
	public function classedit_do() {
		parent::userauth(47);
		if (request()->isAjax()) {
			$data = array();
			$ID                  = input('post.id');
			$data['ClassName']   = input('post.classname');
			$data['Description'] = input('post.description');

			$b=input('post.classname');

			$newsclass = new \app\common\model\Newsclass;
			//获取项目名传给competence ，作为Cname识别
			$cn=$newsclass->where('ID',$ID)->column('ClassName');
			//自动创建并验证数据
			if ($newsclass->save($data,"ID=".$ID)) {
				parent::operating(request()->path(),0,'更新成功');
				//修改项目分类时，自动更新competence表Sid=91的项目分类
				$com=new \app\common\model\Competence;
        		// $com->save(['Cname'=>input('post.classname')],['Cname'=>$cn]);
        		$com->save(['Cname'=>$b],['Cname'=>$cn[0]]);
				//#####################################################@@@@@@@@
				return array('s'=>'ok');
			}else {
				parent::operating(request()->path(),1,'更新失败'.$newsclass->getError());
				return array('s'=>$newsclass->getError());
			}
		}else {
			parent::operating(request()->path(),1,'非法请求');
			return array('s'=>'非法请求');
		}
	}
	//删除分类
	public function classdel() {
		//验证用户权限
		parent::userauth(48);
		//判断是否是ajax请求
		if (request()->isAjax()) {
			$id = input('post.id');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(request()->path(),1,'参数错误');
				return array('s'=>'参数ID类型错误');
			}else {
				$id=intval($id);
				$newsclass= new \app\common\model\Newsclass;
				if ($newsclass->where("ID=$id")->delete()) {
					parent::operating(request()->path(),0,'删除成功');
					return array('s'=>'ok');
				}else {
					parent::operating(request()->path(),1,'删除失败');
					return array('s'=>'数据不存在');
				}
			}
		}else {
			parent::operating(request()->path(),1,'非法请求');
			return array('s'=>'非法请求');
		}
	}
	
	//内容管理
	public function news() {		
		parent::userauth2(84);
	    $where = array();
		$sid   = input('request.sid');
		// echo $sid;
		if($sid){
			$where['Sid']=$sid;
		}
		$tim=input('post.tim');
		$sel=input('post.sel');
		$cond=input('post.cond');

		$selects=array();
		$map=array();
		if(!$tim==''){
			//处理TP5时间范围字符串
			$time1=substr($tim,0,19);
			$time2=substr($tim,21,35);
			$map['Dtime']=array('between',[$time1,$time2]);
		}
		if(!$sel==''){
			//
			if(!input('post.cond')==''){
				$condition='%'.input('post.cond').'%';
				$selects[input('post.sel')]=array('like',$condition);
			}	
		}
		// echo $sid;exit;
		$news  = new \app\common\model\News;
		$user  = new \app\common\model\User;
		$newsclass= new \app\common\model\Newsclass;
		
       $Com=explode(',',Session::get('ThinkUser.Competence'));
       $obj=new \app\common\model\Competence;
       // $news  = new \app\common\model\News;
       $obj2=$obj->where('Sid',91)->Column('ID','Cname');
       // echo $obj->getLastSql();

       $newsclass=new \app\common\model\Newsclass;
       $re2=$newsclass->Column('ID','ClassName');

       //数组交集 
      $result= array_intersect($obj2,array_intersect($Com,$obj2));
      $ary=[];
      foreach($result as $key=>$val){
      	 $ary[]=$re2[$key];
      }
	  if($sel==''){
	  		$lists=$news->where('Sid','IN',$ary)->where($where)->where($map)->order('Dtime desc')->paginate(4);

	  }elseif($selects==''){
			$lists=$news->where('Sid','IN',$ary)->where($where)->where($selects)->order('Dtime desc')->paginate(4);
	  }else{
			$lists=$news->where('Sid','IN',$ary)->where($where)->where($selects)->order('Dtime desc')->where($map)->paginate(4);
	  }
      
	  $volist = $lists->toArray();

      foreach($volist['data'] as $k=>$v){
		    $volist['data'][$k]['Username']   = $user->where("ID=".$v['Uid'])->value('Username');
		    $volist['data'][$k]['ClassName']  = $newsclass->where("ID=".$v['Sid'])->value('ClassName');
		}
		$clist = $newsclass->order('ID asc')->select();
		Session::set('excel',$volist['data']);

		$this->assign('volist',$volist);
		$this->assign('sid',$sid);
		$this->assign('clist',$clist);
		$this->assign('lists',$lists);
       return $this->fetch();
	}


	//打印处理数据
	public function export(){
		$ex=input('request.ex');
		if($ex==1){
			parent::userauth6(128);
			$d=Session::get('excel');
			Session::delete('sid');
		    $this->excel($d);

		}else{
			$this->error("非法请求！");
		}

	}
	public function excel($d=null){ 
		 isset($d)?$d:$this->error("非法请求！");
		 $excelHead = "这个是Excel表格标题"; 
		 $title = "";   #文件命名
		 
		 $titlename = "<tr style='color:white'> 
	                <th style='width:100px;height:46px;background:#038A7C;'>编号</th> 
	               <th style='width:150px;background:#038A7C'>所属分类</th> 
	                <th style='width:158px;background:#038A7C'>姓名</th> 
	                <th style='width:121px;background:#038A7C'>手机号</th>
	                <th style='width:265px;background:#038A7C'>留言内容</th>
	                <th style='width:148px;background:#038A7C'>QQ/EMail/微信</th>
	                <th style='width:160px;background:#038A7C'>地址</th>
	                <th style='width:60px;background:#038A7C'>排序ID</th>
	                <th style='width:175px;background:#038A7C'>时间</th>

	             </tr>"; 
	    $filename = date('Y-m-d h:i:s',time()).".xls";


         #xmlns即是xml的命名空间
         $str = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\nxmlns=\"http://www.w3.org/TR/REC-html40\">\r\n<head>\r\n<meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">\r\n</head>\r\n<body>"; 
	         #以下构建一个html类型格式的表格
	         // $str .= $title; 
	     $str .="<table border=1><head>".$titlename."</head>"; 
	     foreach ($d  as $key=> $rt ) 
	         { 
	             $str .= "<tr style='height:38px'>"; 
	             $str .= "<td>{$rt['ID']}</td>";
	             $str .= "<td>{$rt['ClassName']}</td>";
	             $str .= "<td>{$rt['name']}</td>";
	             $str .= "<td>{$rt['phone']}</td>";
	             $str .= "<td>{$rt['Content']}</td>";
	             $str .= "<td>{$rt['contact']}</td>";
	             $str .= "<td>{$rt['ADDR']}</td>";
	             $str .= "<td>{$rt['Sortid']}</td>";
	             $str .= "<td>{$rt['Dtime']}</td>";
 	             $str .= "</tr>\n"; 
	         } 
	         $str .= "</table></body></html>"; 
	         header( "Content-Type: application/vnd.ms-excel; name='excel'" );   #类型
	         header( "Content-type: application/octet-stream" );     #告诉浏览器响应的对象的类型（字节流、浏览器默认使用下载方式处理）
	         header( "Content-Disposition: attachment; filename=".$filename );   #不打开此文件，刺激浏览器弹出下载窗口、下载文件默认命名
	         header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" ); 
	         header( "Pragma: no-cache" );   #保证不被缓存或者说保证获取的是最新的数据
	         header( "Expires: 0" ); 
	         exit( $str ); 

	     }


    public function estatus(){
    	parent::userauth2(84);
    	$status=input('get.st');
    	$status= isset($status)?1:0;
    	$id=input('get.id');
    	if ($id =='' && !is_numeric($id)) {
			$this->error('参数错误');
		}
		$id = intval($id);
		$news = new \app\common\model\News;
		$news->where('ID',$id)->update(['status'=>$status]);
		$this->success("已读马上为您跳转！");
    }




	//添加内容
	public function add() {
		parent::win_userauth(49);
		$newsclass = new \app\common\model\Newsclass;
		$clist = $newsclass->order('Dtime asc')->select();
		$this->assign('clist',$clist);
		//排序ID
		$news = $user = new \app\common\model\News;
		$Sortid = $news->count()+1;
		$this->assign('Sortid',$Sortid);
		return $this->fetch();
	}
	//添加处理
	public function add_do() {
		parent::userauth2(49);
		$request = Request::instance();
		if (request()->isPost()) {
			$data = array();
			$data['Sid']     = input('post.Sid');
			// $data['Title']   = htmlspecialchars(input('post.Title'));
			//$data['Aliases'] = htmlspecialchars($_POST['Aliases']);
			$data['name']      = input('post.name');
			$data['phone']      = input('post.phone');
			$data['Sortid']      = input('post.Sortid');
			$data['contact']      = input('post.contact');
			// $data['Description'] = htmlspecialchars(input('post.Description'));
			$data['Content']     = input('post.Content');
			$data['ADDR']     = input('post.ADDR');
			$data['Dtime']     = date('y-m-d H:i:s',time());
			$news = new \app\common\model\News;
			//自动创建并验证数据
			if ($news->data($data)) {
				$news->save();
				parent::operating($request->path(),0,'新增成功');
				$this->success('添加成功',url('News/news'),3);
			}else {
				parent::operating($request->path(),1,'新增失败'.$news->getError());
				$this->error($news->getError());
			}
		}else {
			parent::operating($request->path(),1,'非法请求');
			$this->error('非法请求');
		}
	}
	//更新
	public function edit() {
		parent::win_userauth(50);
		$id = input('get.id');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(request()->path(),1,'参数错误');
			$this->error('参数ID类型错误');
		}
		$id=intval($id);
		$news= new \app\common\model\News;
		if ($result=$news->where("ID=$id")->find()) {
			//分类数据
			$newsclass = new \app\common\model\Newsclass;
			$clist = $newsclass->order('ID asc')->select();
			$this->assign('result',$result);
			$this->assign('clist',$clist);
			return $this->fetch();
		}else {
			parent::operating(request()->path(),1,'数据不存在');
			$this->error('没有找到相关数据');
		}
	}
	//修改处理
	public function edit_do() {
		parent::userauth2(50);
		if (request()->isPost()) {
			$data = array();
			$ID                  = input('post.ID');
			$data['Sid']         = input('post.Sid');
			// $data['Title']       = htmlspecialchars(input('post.Title'));
			$data['Sortid']      = input('post.Sortid');
			// $data['Description'] = htmlspecialchars(input('post.Description'));
			$data['name']      = input('post.name');
			$data['Content']     = input('post.Content');
			$data['phone']	     = input('post.phone');
			$data['contact']	 = input('post.contact');
			$data['ADDR']		 = input('post.ADDR');
			$data['Dtime']     = date('y-m-d H:i:s',time());
			$news = new \app\common\model\News;
			//自动创建并验证数据
			if ($news->save($data,"ID=".$ID)) {
				parent::operating(request()->path(),0,'更新成功');
				$this->success('修改成功',url('News/news'),3);
			}else {
				parent::operating(request()->path(),1,'更新失败：'.$news->getError());
				$this->error($news->getError());
			}
		}else {
			parent::operating(request()->path(),1,'非法请求');
			$this->error('非法请求');
		}
	}
	
	public function article() {
		$id = input('get.id');
		if ($id =='' && !is_numeric($id)) {
			$this->error('参数错误');
		}
		$id = intval($id);
		$news = new \app\common\model\News;
		$user = new \app\common\model\User;
		$result=$news->where("ID = $id")->find();
		$result['Username'] = $user->where("ID='{$result['Uid']}'")->value("Username");
		$news->where("ID = $id")->setInc('View');
		//分类数据
		$newsclass = new \app\common\model\Newsclass;
		$clist = $newsclass->order('ID asc')->select();
		$this->assign('result',$result);
		$this->assign('clist',$clist);
		return $this->fetch();
	}
	//删除
	public function newsdel() {
		//验证用户权限
		parent::userauth(51);
		//判断是否是ajax请求
		if (request()->isAjax()) {
			$id = input('post.id');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(request()->path(),1,'参数错误');
				return array('s'=>'参数ID类型错误');
			}else {
				$id=intval($id);
				$news= new \app\common\model\News;
				if ($news->where("ID=$id")->delete()) {
					parent::operating(request()->path(),0,'删除成功');
					return array('s'=>'ok');
				}else {
					parent::operating(request()->path(),1,'删除失败');
					return array('s'=>'数据不存在');
				}
			}
		}else {
			parent::operating(request()->path(),1,'非法请求');
			return array('s'=>'非法请求');
		}
	}
	//批量删除
	public function newsindel() {
		//验证用户权限
		parent::userauth(51);
		if (request()->isAjax()) {
			if (!$delid=explode(',',input('post.delid',''))) {
				return array('s'=>'请选中后再删除');
			}
			//将最后一个元素弹出栈
			array_pop($delid);
			$id=join(',',$delid);
			$news= new \app\common\model\News;
			if ($news->where('ID','IN',$id)->delete()) {
				parent::operating(request()->path(),0,'删除成功');
				return array('s'=>'ok');
			}else {
				parent::operating(request()->path(),1,'删除失败');
				return array('s'=>'删除失败');
			}
		}else {
			parent::operating(request()->path(),1,'非法请求');
			return array('s'=>'非法请求');
		}
	}
}
