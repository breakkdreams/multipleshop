<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin','admin',0);
class chatlog extends admin {
	function __construct() {
		parent::__construct();
		$this->chatlog = pc_base::load_model('chatlog_model');
	}

	public function chatloglist() {
	    $shopid = '1';//客服id
        $fid = $_POST['fromuserid'];
        $tid = $_POST['touserid'];
        if(!empty($fid) && empty($tid)){
            $where = 'fromuserid = '.$fid;
        }elseif(empty($fid) && !empty($tid)){
            $where = 'touserid = '.$tid;
        }elseif (!empty($fid) && !empty($tid)){
            $where = '(touserid = '.$tid.' and fromuserid = '.$fid.') or (touserid = '.$fid.' and fromuserid = '.$tid.')';
        }

        $page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
        $infos = $this->chatlog ->listinfo($where,$order = 'create_date desc',$page, $pages = '10');
        $pages = $this->db->pages;

//        for ($i=0;$i<sizeof($infos);$i++){
//
//        }

		include $this->admin_tpl('chatlog_list');
	}


	
}
?>