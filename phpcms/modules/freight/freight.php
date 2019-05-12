<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin','admin',0);
class freight extends admin {
	function __construct() {
		parent::__construct();
		$this->freight = pc_base::load_model('freight_model');
		$this->shipping_way = pc_base::load_model('shipping_way_model');
		$this->region = pc_base::load_model('region_model');
		$this->large_area = pc_base::load_model('large_area_model');
	}

	public function freightlist() {
		$where = ' status = 1 ';
 		$page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
		$infos = $this->freight ->listinfo($where,$order = '',$page, $pages = '10');
		$pages = $this->db->pages;

		for ($i=0; $i < sizeof($infos) ; $i++) { 
			$shippingway = $this->shipping_way->get_one(array('template_id'=>$infos[$i]['template_id']));
			$infos[$i]['first_num'] = $shippingway['first_num'];
			$infos[$i]['continue_num'] = $shippingway['continue_num'];
			$infos[$i]['first_fee'] = $shippingway['first_fee'];
			$infos[$i]['continue_fee'] = $shippingway['continue_fee'];
		}
		$str = $_REQUEST['pc_hash'];

		$big_menu = array("javascript:window.location.href='?m=freight&c=freight&a=addpage&pc_hash=$str'", "添加");
		include $this->admin_tpl('freight_list');
	}

	//添加页面
	public function addpage() {
		//城市
		$country = $this->region->get_one(array('parent_id'=>0));
		include $this->admin_tpl('freight_add');
   }

   //获取省市区
	public function regionlist() {
		$parent = $_GET['parent'];
		//三级联动
		$regionlist = $this->region->select(array('parent_id'=>$parent));
		exit(json_encode($regionlist,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
   }
    //获取区域
	public function getarea() {
		$area = $this->large_area->select('');
		exit(json_encode($area,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
   }

	//添加
 	public function add() {
		$_POST['link']['addtime'] = SYS_TIME;
		$_POST['link']['siteid'] = $this->get_siteid();
		if(empty($_POST['link']['name'])) {
			showmessage(L('sitename_noempty'),HTTP_REFERER);
		} else {
			$_POST['link']['name'] = safe_replace($_POST['link']['name']);
		}
		if ($_POST['link']['logo']) {
			$_POST['link']['logo'] = safe_replace($_POST['link']['logo']);
		}
		$data = new_addslashes($_POST['link']);
		$linkid = $this->db->insert($data,true);
		if(!$linkid) return FALSE; 
		$siteid = $this->get_siteid();
		//更新附件状态
		if(pc_base::load_config('system','attachment_stat') & $_POST['link']['logo']) {
			$this->attachment_db = pc_base::load_model('attachment_model');
			$this->attachment_db->api_update($_POST['link']['logo'],'link-'.$linkid,1);
		}
		showmessage(L('operation_success'),HTTP_REFERER,'', 'add');

	}
	
	
	
	/**
	 * 删除分类
	 */
	public function delete_type() {
		if((!isset($_GET['typeid']) || empty($_GET['typeid'])) && (!isset($_POST['typeid']) || empty($_POST['typeid']))) {
			showmessage(L('illegal_parameters'), HTTP_REFERER);
		} else {
			if(is_array($_POST['typeid'])){
				foreach($_POST['typeid'] as $typeid_arr) {
 					$this->db2->delete(array('typeid'=>$typeid_arr));
				}
				showmessage(L('operation_success'),HTTP_REFERER);
			}else{
				$typeid = intval($_GET['typeid']);
				if($typeid < 1) return false;
				$result = $this->db2->delete(array('typeid'=>$typeid));
				if($result)
				{
					showmessage(L('operation_success'),HTTP_REFERER);
				}else {
					showmessage(L("operation_failure"),HTTP_REFERER);
				}
			}
		}
	}
	
	//:分类管理
 	public function list_type() {
		$this->db2 = pc_base::load_model('type_model');
		$page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
		$infos = $this->db2->listinfo(array('module'=> ROUTE_M,'siteid'=>$this->get_siteid()),$order = 'listorder DESC',$page, $pages = '10');
		$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=link&c=link&a=add\', title:\''.L('link_add').'\', width:\'700\', height:\'450\'}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);', L('link_add'));
		$pages = $this->db2->pages;
		include $this->admin_tpl('link_list_type');
	}
 
	public function edit() {
		if(isset($_POST['dosubmit'])){
 			$linkid = intval($_GET['linkid']);
			if($linkid < 1) return false;
			if(!is_array($_POST['link']) || empty($_POST['link'])) return false;
			if((!$_POST['link']['name']) || empty($_POST['link']['name'])) return false;
			$this->db->update($_POST['link'],array('linkid'=>$linkid));
			//更新附件状态
			if(pc_base::load_config('system','attachment_stat') & $_POST['link']['logo']) {
				$this->attachment_db = pc_base::load_model('attachment_model');
				$this->attachment_db->api_update($_POST['link']['logo'],'link-'.$linkid,1);
			}
			showmessage(L('operation_success'),'?m=link&c=link&a=edit','', 'edit');
			
		}else{
 			$show_validator = $show_scroll = $show_header = true;
			pc_base::load_sys_class('form', '', 0);
			$types = $this->db2->listinfo(array('module'=> ROUTE_M,'siteid'=>$this->get_siteid()),$order = 'typeid DESC');
 			$type_arr = array ();
			foreach($types as $typeid=>$type){
				$type_arr[$type['typeid']] = $type['name'];
			}
			//解出链接内容
			$info = $this->db->get_one(array('linkid'=>$_GET['linkid']));
			if(!$info) showmessage(L('link_exit'));
			extract($info); 
 			include $this->admin_tpl('link_edit');
		}

	}
	
	/**
	 * 修改友情链接 分类
	 */
	public function edit_type() {
		if(isset($_POST['dosubmit'])){ 
			$typeid = intval($_GET['typeid']); 
			if($typeid < 1) return false;
			if(!is_array($_POST['type']) || empty($_POST['type'])) return false;
			if((!$_POST['type']['name']) || empty($_POST['type']['name'])) return false;
			$this->db2->update($_POST['type'],array('typeid'=>$typeid));
			showmessage(L('operation_success'),'?m=link&c=link&a=list_type','', 'edit');
			
		}else{
 			$show_validator = $show_scroll = $show_header = true;
			//解出分类内容
			$info = $this->db2->get_one(array('typeid'=>$_GET['typeid']));
			if(!$info) showmessage(L('linktype_exit'));
			extract($info);
			include $this->admin_tpl('link_type_edit');
		}

	}

	/**
	 * 删除友情链接  
	 * @param	intval	$sid	友情链接ID，递归删除
	 */
	public function delete() {
  		if((!isset($_GET['linkid']) || empty($_GET['linkid'])) && (!isset($_POST['linkid']) || empty($_POST['linkid']))) {
			showmessage(L('illegal_parameters'), HTTP_REFERER);
		} else {
			if(is_array($_POST['linkid'])){
				foreach($_POST['linkid'] as $linkid_arr) {
 					//批量删除友情链接
					$this->db->delete(array('linkid'=>$linkid_arr));
					//更新附件状态
					if(pc_base::load_config('system','attachment_stat')) {
						$this->attachment_db = pc_base::load_model('attachment_model');
						$this->attachment_db->api_delete('link-'.$linkid_arr);
					}
				}
				showmessage(L('operation_success'),'?m=link&c=link');
			}else{
				$linkid = intval($_GET['linkid']);
				if($linkid < 1) return false;
				//删除友情链接
				$result = $this->db->delete(array('linkid'=>$linkid));
				//更新附件状态
				if(pc_base::load_config('system','attachment_stat')) {
					$this->attachment_db = pc_base::load_model('attachment_model');
					$this->attachment_db->api_delete('link-'.$linkid);
				}
				if($result){
					showmessage(L('operation_success'),'?m=link&c=link');
				}else {
					showmessage(L("operation_failure"),'?m=link&c=link');
				}
			}
			showmessage(L('operation_success'), HTTP_REFERER);
		}
	}
	
	/**
	 * 说明:对字符串进行处理
	 * @param $string 待处理的字符串
	 * @param $isjs 是否生成JS代码
	 */
	function format_js($string, $isjs = 1){
		$string = addslashes(str_replace(array("\r", "\n"), array('', ''), $string));
		return $isjs ? 'document.write("'.$string.'");' : $string;
	}
 
 
	
}
?>