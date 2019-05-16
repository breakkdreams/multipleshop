<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin', 'admin', 0);
pc_base::load_sys_class('format', '', 0);
pc_base::load_sys_class('form', '', 0);
pc_base::load_app_func('global');

class goods extends admin
{
    static $shopLabel = array("shopname"=>"", "sketch"=>"","typeID"=>"","price"=>"", "repertory"=>"","putaway"=>"","thumb"=>"", "content"=>"", "infopicture"=>"", "addtime"=>"", "sort"=>"", "unspecification"=>"","specification"=>"", "shoppicture"=>"");
    //shopname商品名，sketch商品简述，typeID商品类型，price价格，repertory库存，putaway是否上架，thumb缩略图，content详细时间，picture详细图片地址，addtime添加时间，sort商品排序，unspecification未处理的商品规格
    function __construct()
    {
        parent::__construct();
        $this->shop = pc_base::load_model("zyshop_model");
        $this->shoptype = pc_base::load_model("zyshoptype_model");
        $this->member = pc_base::load_model("member_model");
        $this->tqinfo = pc_base::load_model("zytqinfo_model");
        $this->zenyu = pc_base::load_model("zenyu_model");
        $this->pagesize = 10;
    }

    public function drop()
    {
        if(isset($_GET["id"]))
            $shopID = $_GET["id"];
        else
            showmessage("操作失败，请输入ID", 'index.php?m=zymanagement&c=goods&a=capitaldetails', '', '');
        $this->shop->delete(array("shopID"=>$shopID));
        showmessage("操作成功", 'index.php?m=zymanagement&c=goods&a=capitaldetails', '', '');
    }
    public function getPageDate($page, $infos)
    {
        $arrayCount = count($infos);
        $info = null;
        $pagenums = ($arrayCount%$this->pagesize) == 0? ($arrayCount/$this->pagesize): (int)($arrayCount/$this->pagesize)+1;//总页数
        if($page > $pagenums)
            $page = 1;
        for($i = ($page-1)*$this->pagesize; ($i < $page*$this->pagesize) && ($i < $arrayCount) ; $i++)
            $info[] = $infos[$i];

        return array($pagenums, $info, $arrayCount);
    }
    public function capitaldetails()
    {
        $page = isset($_GET["page"])?intval($_GET["page"]): 1;
        $sql = "select B1.*, B2.typename from `zy_zyshop`B1 left join `zy_zyshoptype` B2 ON B1.typeID=B2.typeID WHERE ";

        $where = "";
        $shopname = isset($_GET["shopname"])?$_GET["shopname"]:"";
        $start_addtime = isset($_GET["start_addtime"])?strtotime($_GET["start_addtime"]):"";
        $end_addtime = isset($_GET["end_addtime"])?strtotime($_GET["end_addtime"]):"";
        $type = isset($_GET["type"])?$_GET["type"]:"";
        $status = isset($_GET["status"])?$_GET["status"]:"";//一些筛选字段
        if(!empty($shopname)){
            $where .= "B1.shopname like '%$shopname%' AND ";
        }
        if(!empty($start_addtime))
            $where .= "B1.addtime > '$start_addtime' AND ";
        if(!empty($end_addtime))
            $where .= "B1.addtime < '$end_addtime' AND ";
        if(!empty($type))
            $where .= "B1.typeID = '$type' AND ";
        if(!empty($status) || $status=='0')
            $where .= "B1.putaway = ".$status." AND ";
        $where .= "1";
        $infos = $this->shop->spcSql($sql.$where, 1, 1);
        $shoptype = $this->shoptype->select("1");
        list($pagenums, $infos, $arrayCount) = $this->getPageDate($page, $infos);
        include $this->admin_tpl('capitaldetails');
    }
    public function shopadd()
    {
        if(isset($_POST["dosubmit"]))
        {
           $pic = "";
           $shopPic = "";
           $_POST["info"]["unspecification"] = $specification = str_replace("，",",", $_POST["info"]["unspecification"]);
           foreach($_POST["info"] as $key=>$value)
           {
               if(array_key_exists($key, self::$shopLabel))
                    self::$shopLabel[$key] = $value;
           }
           if(count($_POST["pictureurls_url"])!= count($_POST["pictureurls_alt"]))
               showmessage("操作失败", '', '', 'add');
           if(count($_POST["shopPictureurls_url"])!= count($_POST["shopPictureurls_alt"]))
                showmessage("操作失败", '', '', 'add');
           if(isset($_POST["pictureurls_url"]))
           {
               foreach($_POST["pictureurls_url"] as $key=>$value)
               {
                   $pic .= $_POST["pictureurls_alt"][$key]."|";
                   $pic .= $value.",";
               }
                self::$shopLabel["infopicture"] = $pic;

           }
            if(isset($_POST["shopPictureurls_url"]))
            {
                foreach($_POST["shopPictureurls_url"] as $key=>$value)
                {
                    $shopPic .= $_POST["shopPictureurls_alt"][$key]."|";
                    $shopPic .= $value.",";
                }
                self::$shopLabel["shoppicture"] = $shopPic;
            }
           self::$shopLabel["addtime"] = time();
           self::$shopLabel["specification"] = json_encode(explode(",",$specification),JSON_UNESCAPED_UNICODE);
           $this->shop->insert(self::$shopLabel);
           showmessage(L('operation_success'), '', '', 'add');
        }
        else
        {
            $type = $this->shoptype->select(array("isshow"=>1));
            $upload_allowext = 'jpg|jpeg|gif|png|bmp';
            $isselectimage = '1';
            $images_width = '';
            $images_height = '';
            $watermark = '0';
            $authkey = upload_key("1,$upload_allowext,$isselectimage,$images_width,$images_height,$watermark");
            $authkey_1 = upload_key("10,$upload_allowext,$isselectimage,$images_width,$images_height,$watermark");
            include $this->admin_tpl("addshop");
        }
    }
    public function edit()
    {
        if($_POST["dosubmit"])
        {
            $shopID = $_POST["shopID"];
            $pic = "";
            $shopPic = "";
            $_POST["info"]["unspecification"] = $specification = str_replace("，",",", $_POST["info"]["unspecification"]);
            foreach($_POST["info"] as $key=>$value)
            {
                if(array_key_exists($key, self::$shopLabel))
                    self::$shopLabel[$key] = $value;
            }
            if(count($_POST["pictureurls_url"])!= count($_POST["pictureurls_alt"]))
                showmessage("操作失败", '', '', 'edit');
            if(count($_POST["shopPictureurls_url"])!= count($_POST["shopPictureurls_alt"]))
                showmessage("操作失败", '', '', 'edit');
            if(isset($_POST["pictureurls_url"]))
            {
                foreach($_POST["pictureurls_url"] as $key=>$value)
                {
                    $pic .= $_POST["pictureurls_alt"][$key]."|";
                    $pic .= $value.",";
                }
                self::$shopLabel["infopicture"] = $pic;
            }
            if(isset($_POST["shopPictureurls_url"]))
            {
                foreach($_POST["shopPictureurls_url"] as $key=>$value)
                {
                    $shopPic .= $_POST["shopPictureurls_alt"][$key]."|";
                    $shopPic .= $value.",";
                }
                self::$shopLabel["shoppicture"] = $shopPic;
            }
            self::$shopLabel["specification"] = json_encode(explode(",",$specification),JSON_UNESCAPED_UNICODE);
            $this->shop->update(self::$shopLabel, array("shopID"=>$shopID));
            showmessage(L('operation_success'), '', '', 'edit');
        }
        else
        {
            $id = isset($_GET["id"])?$_GET["id"]:"1";
            $info = $this->shop->spcSql("select * from `zy_zyshop` where `shopID`=".$id.";", 1);
            $shopType= $this->shoptype->select("1");
            $picture = string_array($info["infopicture"]);
            $picture = pictury_array($picture);
            $shopPicture = string_array($info["shoppicture"]);
            $shopPicture = pictury_array($shopPicture);
            $upload_allowext = 'jpg|jpeg|gif|png|bmp';
            $isselectimage = '1';
            $images_width = '';
            $images_height = '';
            $watermark = '0';
            $authkey = upload_key("1,$upload_allowext,$isselectimage,$images_width,$images_height,$watermark");
            $authkey_1 = upload_key("10,$upload_allowext,$isselectimage,$images_width,$images_height,$watermark");
            include $this->admin_tpl("editshop");
        }

    }
    //******************************************************************************************************************
    //TQ币操作，因为数据库加载比较齐，所以放这里
    function boosGive()
    {	$title=$_POST['title'];
//		$sql = $this->zenyu->get_one(array('id'=>'1'),"title1");
//	 	 $infos = json_decode($sql['title1'],ture);

        $infos = $this->zenyu->select();
        if($_POST["dosubmit"])
        {
            $neadArg = ["username" => [true, 0], "amount" => [true, 1]];
            $info = checkArgBcak($neadArg, "POST");
            $where = "1 ";
            if (is_numeric($info["username"]) && (strlen($info["username"]) == 11))
                $where .= " AND `mobile`=" . $info["username"];
            else
                $where .= " AND `wechat_name`='" . $info["username"]."'";
            $data = $this->member->select($where, "wechat_name, userid");
			
			
            if($data == null || $data[0]["islock"] == 1)
                showmessage("用户不存在", '?m=zymanagement&c=goods&a=boosGive', '3000', '');
            elseif(count($data) != 1)
                showmessage("有多个用户", '?m=zymanagement&c=goods&a=boosGive', '3000', '');
			
			
            $this->member->update(array("TQ"=>"+=".$info["amount"]),$where);
			
            $tqInfo = array("userid"=>$data[0]["userid"],"infotype"=>4, "TQ"=>$info["amount"], "addtime"=>time(),"info"=>$title);
            $this->tqinfo->insert($tqInfo);
			
            showmessage(L('operation_success'), '?m=zymanagement&c=goods&a=boosGive', '1500', '');
			
			
        }
        else
        {
            $info = $this->zenyu->select();
			$grideInfo =json_decode($info["title1"],true);
            include $this->admin_tpl("recharge");
        }
    }
	
	
    function leaveGive()
    {
        if($_POST["dosubmit"])
        {
            $neadArg = ["leaveusername" => [true, 0], "giveusername" => [true, 0]];
            $info = checkArgBcak($neadArg, "POST");
            $where_leave = "1 ";
            $where_give = "1 ";
            if (is_numeric($info["leaveusername"]) && (strlen($info["leaveusername"]) == 11))
                $where_leave .= " AND `mobile`=" . $info["leaveusername"];
            else
                $where_leave .= " AND `wechat_name`='" . $info["leaveusername"]."'";
            $data_leave = $this->member->select($where_leave, "wechat_name, userid, TQ, islock");

            if($data_leave == null  || $data_leave[0]["islock"] == 1)
                showmessage("离职用户不存在", '?m=zymanagement&c=goods&a=leaveGive', '3000', '');
            elseif(count($data_leave) != 1)
                showmessage("有多个离职用户", '?m=zymanagement&c=goods&a=leaveGive', '3000', '');


            if (is_numeric($info["giveusername"]) && (strlen($info["giveusername"]) == 11))
                $where_give .= " AND `mobile`=" . $info["giveusername"];
            else
                $where_give .= " AND `wechat_name`='" . $info["leaveusername"]."'";


            $data_give = $this->member->select($where_give, "wechat_name, userid,islock");
            if($data_give == null || $data_give[0]["islock"] == 1)
                showmessage("赠与用户不存在", '?m=zymanagement&c=goods&a=leaveGive', '3000', '');
            elseif(count($data_give) != 1)
                showmessage("有过个赠与用户", '?m=zymanagement&c=goods&a=leaveGive', '3000', '');

            if($data_leave[0]["userid"] == $data_give[0]["userid"])
                showmessage("不能对同一个用户操作", '?m=zymanagement&c=goods&a=leaveGive', '3000', '');

            $this->member->update(array("TQ"=>"+=".$data_leave[0]["TQ"]),$where_give);
            $tqInfo = array("userid"=>$data_give[0]["userid"],"infotype"=>3, "TQ"=>$data_leave[0]["TQ"], "addtime"=>time(),"info"=>$data_leave[0]["wechat_name"]);
            $this->tqinfo->insert($tqInfo);
            $this->member->update(array("islock"=>1, "TQ"=>0), $where_leave);
            showmessage(L('operation_success'), '?m=zymanagement&c=goods&a=leaveGive', '1500', '');
        }
        else
        {
            include $this->admin_tpl("leave_recharge");
        }
    }
	
	
	
//赠与标题	
	function title_type(){
        $title=$_POST['titleID'];
        $info = $this->zenyu->select();
        $isid = '';
        for ($i=1; $i<=sizeof($title); $i++) {
            if($title[$i]['id'] == null){
                $this->zenyu->insert(array('title1'=>$title[$i]['TitleContent']));
            }else{
                $this->zenyu->update(array('title1'=>$title[$i]['title1']),array("id"=>$title[$i]['id']));
            }
            if($isid !=''){
                $isid=$isid.',';
            }
            $isid = $isid.$title[$i]['id'];
        }

        for ($j=0; $j<=sizeof($info); $j++){
            if(strpos($isid,$info[$j]['id']) !==false){

            }else{
                $this->zenyu->delete(array("id"=>$info[$j]['id']));
            }

        }

	    showmessage(L("操作成功"),HTTP_REFERER);
    }
}