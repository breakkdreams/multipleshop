<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin','admin',0);

class module extends admin {
	private $db;
	
	public function __construct() {
		$this->db = pc_base::load_model('module_model');
		parent::__construct();
	}
	
	public function init() {
		$file_down = pc_base::load_app_class('file_down');
		$dirs = $module = $dirs_arr = $directory = array();
		
		$dirs = glob(PC_PATH.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'*');
		//var_dump(PC_PATH.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'*');
		//exit;
		
		
		foreach ($dirs as $d) {
			if (is_dir($d)) {
				$d = basename($d);
				
				$dirs_arr[] = $d;
			}
		}
		define('INSTALL', true);
		$modules = $this->db->select('', '*', '', '', '', 'module');
		
		$downname='http://192.168.1.66/file/downname.txt';
		$ycdirs=$file_down->opentxt($downname);
		
		foreach ($ycdirs as $c){
			 $arr = explode('--',$c);
			$ycname[]=iconv("gb2312","utf-8",$arr[1]);
			$ycversion[]=$arr[2];
			
		 if (!in_array($arr[0],$dirs_arr)){
			 $dirs_arr[] = $arr[0];
			 }
		 
			}
		
	
		$total = count($dirs_arr)+count($ycdirs);
	
		$dirs_arr = array_chunk($dirs_arr, 20, true);
	//	var_dump($dirs_arr);
		//exit;
		$page = max(intval($_GET['page']), 1);
		$pages = pages($total, $page, 20);
		
		$directory = $dirs_arr[intval($page-1)];
	//	var_dump($directory);
		include $this->admin_tpl('module_list');
	}
	
	/**
	 * 模块安装
	 */
	public function install() {
		
		$this->module = $_POST['module'] ? $_POST['module'] : $_GET['module'];
		$module_api = pc_base::load_app_class('module_api');
		if (!$module_api->check($this->module)) showmessage($module_api->error_msg, 'blank');
		if ($_POST['dosubmit']) {
			if ($module_api->install()) showmessage(L('success_module_install').L('update_cache'), '?m=admin&c=module&a=cache&pc_hash='.$_SESSION['pc_hash']);
			else showmesage($module_api->error_msg, HTTP_REFERER);
		} else {
			include PC_PATH.'modules'.DIRECTORY_SEPARATOR.$this->module.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'config.inc.php';
			include $this->admin_tpl('module_config');
		}
	}
	
	/**
	 * 模块卸载
	 */
	public function uninstall() {
		if(!isset($_GET['module']) || empty($_GET['module'])) showmessage(L('illegal_parameters'));
		
		$module_api = pc_base::load_app_class('module_api');
		if(!$module_api->uninstall($_GET['module'])) showmessage($module_api->error_msg, 'blank');
		else showmessage(L('uninstall_success'), '?m=admin&c=module&a=cache&pc_hash='.$_SESSION['pc_hash']);
	}
	
	/**
	 * 更新模块缓存
	 */
	public function cache() {
		echo '<script type="text/javascript">parent.right.location.href = \'?m=admin&c=cache_all&a=init&pc_hash='.$_SESSION['pc_hash'].'\';window.top.art.dialog({id:\'install\'}).close();</script>';
		//showmessage(L('update_cache').L('success'), '', '', 'install');
	}
	
	public function downcj(){
		$file_down = pc_base::load_app_class('file_down');
		//var_dump($file_down->cs());
	//	exit;
		$pass=$_POST['pass'];
		if($_POST['pass']==null){
		include $this->admin_tpl('module_down');
			}else{
		//验证密码	
			$url="http://192.168.1.66/file/yz.php";  
		//	require('http://192.168.1.65/file/yz.php');
			$pass=md5($pass);
			$url='http://192.168.1.66/file/yz.php?pass='.$pass; 
			$con=file_get_contents($url);
			if ($con==false){
				echo '密码错误，无权下载';
				}else{
			$mname=$_POST['module'];
		
			$downurl=$con.'downurl.txt';
			$ycdirs=$file_down->opentxt($downurl);
		
		foreach ($ycdirs as $c){
			 $arr = explode('--',$c);
			 if ($mname==$arr[0]){
				 $zh=trim($arr[1]);
			$ycname=iconv("gb2312","utf-8//IGNORE",$zh);
		
			 }
			}
		$url=$con.urlencode(iconv("UTF-8","GBK//IGNORE",$ycname));
		
	
		$save_dir = "api/cj/";  
		$filename =$mname.'.zip';  
		$cc=$save_dir.$filename;
		$aa=is_file($cc);
		if ($aa==true){
		showmessage('文件已存在，请勿重复下载', '?m=admin&c=module&a=cache&pc_hash='.$_SESSION['pc_hash']);
		
		}else{
		$res = $file_down->getFile($url, $save_dir, $filename,1);//0  1 都是好使的 
		$size = $file_down->get_zip_originalsize($cc,'phpcms/modules/'); 
		showmessage('下载成功', '?m=admin&c=module&a=cache&pc_hash='.$_SESSION['pc_hash']);
	
} 
			}
		
		
			}
		}
	
	
}
?>