<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_func('global');
pc_base::load_sys_class('form', '', 0);
pc_base::load_sys_class('format', '', 0);
/**
 * Created by PhpStorm.
 * User: 徐强
 * Date: 2019/4/11
 * Time: 11:43
 */
class memberApi
{
    static $infotype = array('1'=>"兑换", '2'=>"给予", '3'=>"给予", '4'=>"奖励", "5"=>"大转盘抽奖");
    function __construct()
    {
        $this->shop = pc_base::load_model("zyshop_model");
        $this->member = pc_base::load_model("member_model");
        $this->myfriend = pc_base::load_model("zymyfriend_model");
        $this->shoptype = pc_base::load_model("zyshoptype_model");
        $this->buycar = pc_base::load_model("zybuycar_model");
        $this->tqinfo = pc_base::load_model("zytqinfo_model");
        $this->sms_report_db = pc_base::load_model('sms_report_model');
        $this->zyrecvaddress = pc_base::load_model("zyrecvaddress_model");
		$this->zenyu = pc_base::load_model("zenyu_model");
    }
    function checkMember($info)
    {
        $data = $this->member->get_one(array("userid"=>$info["userid"]),  "wechatxcx_openid, TQ, mobile, wechat_name,leader");
        if($data == null)
            returnAjaxData("-1", "不存在该用户");
        if($data["wechatxcx_openid"] != $info["openid"])
            returnAjaxData("-1", "用户信息错误");
        return $data;
    }
    function getMemberInfoAjax()//测试完
    {
        $neadArg = ["openid"=>[true, 1], "userid"=>[true, 1]];
        $info = checkArg($neadArg);
        $memberData = $this->checkMember($info); //检测用户是否正确
        $where["userid"] = array_pop($info);
        $data = $this->member->get_one($where,"userid,headimgurl,wechat_name,wechat_sex,mobile,email,birthday,department,TQ,leader");
        returnAjaxData("1", "成功", $data);
    }
    function updateMemberInfoAjax()//测试完
    {
        $neadArg = ["wechat_name"=>[false, 0], "wechat_sex"=>[false, 0], "email"=>[false, 0], "birthday"=>[false, 0], "department"=>[false, 0], "openid"=>[true, 0], "userid"=>[true, 1]];
        $info = checkArg($neadArg);
        $memberData = $this->checkMember($info); //检测用户是否正确
        $where["userid"] = array_pop($info);
        unset($info["openid"]);
        $this->member->update($info, $where);
        returnAjaxData("1", "修改成功");
    }
	
	
	
	
	
	
	
    function myFriendAjax()//测试完
    {
        $neadArg = ["openid"=>[true, 1],"userid"=>[true, 1]];
        $info = checkArg($neadArg);
        $memberData =  $this->checkMember($info); //检测用户是否正确
        $where["userid"] = array_pop($info);
//        $firendID_str = $this->myfriend->get_one($where, "friendID");
//        if($firendID_str == null)returnAjaxData("0", "空");
        //$friendID = string_array($firendID_str["friendID"]);
        $data = $this->member->select("1", "wechat_name, mobile,department","", "department Desc");
        returnAjaxData("1", "查询成功", $data);
    }
    function FriendAjax()//测试完
    {
        $neadArg = ["type"=>[true,1],"friendID"=>[true, 1], "userid"=>[true, 1]];//type=4加好友，type=3删好友
        $info = checkArg($neadArg);
        if($info["type"]!=3 && $info["type"]!= 4)//只能传入3,4这两个参数
            returnAjaxData("-1", "请传入正确的参数");
        if($info["friendID"] == $info["userid"])//不能添加自己
            returnAjaxData("-1", "好友ID错误");
        $flag = array($info["userid"]=>$info["friendID"], $info["friendID"]=>$info["userid"]);//创建一个数组userid=>friendID,friendID=>userid(方便后面添加数据用)
        //$where["userid"] = array_pop($info);
        $firendInfo = $this->member->select_or(array("userid"=>[$info["friendID"], $info["userid"]]), "userid");
        if(count($firendInfo) != 2)//如果member表没有两条数据，证明有一个用户不存在
            returnAjaxData("-1", "用户不存在");
        $firendID_str = $this->myfriend->select_or(array("userid"=>[$info["userid"], $info["friendID"]]), "*");//取myfriend表
        $check = preg_match("[".$info["friendID"].",]", $firendID_str[$info["userid"]]["friendID"]);//检测是否已经是好友了
        if($check != 0 && $info["type"] == 4)
            returnAjaxData("-1", "该用户已经是你的好友了");
        if($check == 0 && $info["type"] == 3)//删除的时候检测是否是好友
            returnAjaxData("-1", "该用户不是你的好友");
        switch($info["type"])
        {
            case 3:
                foreach($firendID_str as $key=>$value)
                {
                    $update[$value["userid"]] = preg_replace("[".$flag[$value["userid"]].",]", "", $value["friendID"]);//正则删除好友
                }
                break;
            case 4:
                foreach($firendID_str as $key=>$value)
                {
                    $update[$value["userid"]] = $value["friendID"].$flag[$value["userid"]].",";//追加好友
                }
                break;
        }
        foreach($update as $key=>$value)
            $this->myfriend->update(array("friendID"=>$value), array("userid"=>$key));//循环添加
        returnAjaxData("1", "操作成功");
    }
  function leaderGiveAjax()
    {
//	  "openid"=>[true, 1],
        $neadArg = [ "TQ"=>[true, 1], "mobile"=>[true,0], "userid"=>[true, 1],"title"=>["title",0]];//
//        $info = array("TQ"=>$_POST['TQ'], "mobile"=>$_POST['mobile'],"userid"=>$_POST['userid'],"info"=>$_POST['title'],);
       
      	$info = checkArg($neadArg,"POST");
       // $memberData = $this->checkMember($info); //检测用户是否正确
        $friendData = $this->member->get_one(array("mobile"=>$info["mobile"]), "userid ,TQ, wechat_name");
       if($friendData == null)returnAjaxData("-1", "给与用户不存在");
            
        $friendData["TQ"] = intval($friendData["TQ"]) + $info["TQ"];
        $this->member->update(array("TQ"=>$friendData["TQ"]), array("mobile"=>$info["mobile"]));
		
        //$this->tqinfo->insert(array("userid"=>$friendData["userid"], "infotype"=>2, "info"=>$friendData["wechat_name"], "addtime"=>time(), "TQ"=>$info["TQ"]));
        $this->tqinfo->insert(array("userid"=>$friendData["userid"], "infotype"=>4, "info"=>$info["title"], "addtime"=>time(), "TQ"=>$info["TQ"]));
		returnAjaxData("1", "给与成功");
    }
	
	
//    function leaderGiveAjax()
//    {
//        $neadArg = ["openid"=>[true, 1], "TQ"=>[true, 1], "mobile"=>[true,0], "userid"=>[true, 1]];
//        $info = checkArg($neadArg);
//        $memberData = $this->checkMember($info); //检测用户是否正确
//        $friendData = $this->member->get_one(array("mobile"=>$info["mobile"]), "userid ,TQ, wechat_name");
//        if($friendData == null)
//            returnAjaxData("-1", "给与用户不存在");
//        $friendData["TQ"] = intval($friendData["TQ"]) + $info["TQ"];
//        $this->member->update(array("TQ"=>$friendData["TQ"]), array("mobile"=>$info["mobile"]));
//        //$this->tqinfo->insert(array("userid"=>$info["userid"], "infotype"=>2, "info"=>$friendData["wechat_name"], "addtime"=>time(), "TQ"=>$info["TQ"]));
//        $this->tqinfo->insert(array("userid"=>$friendData["userid"], "infotype"=>4, "info"=>$memberData["wechat_name"], "addtime"=>time(), "TQ"=>$info["TQ"]));
//        returnAjaxData("1", "给与成功");
//    }
	
	

	
	
    function giveFriendAjax()//TQ给与，并添加记录
    {
        $neadArg = ["openid"=>[true, 1], "TQ"=>[true, 1], "mobile"=>[true,0], "userid"=>[true, 1]];
       $info = checkArg($neadArg);
       $memberData = $this->checkMember($info); //检测用户是否正确
       $friendData = $this->member->get_one(array("mobile"=>$info["mobile"]), "userid ,TQ, wechat_name");
        if($friendData == null)
            returnAjaxData("-1", "给与用户不存在");
        if(intval($memberData["TQ"]) < $info["TQ"])
            returnAjaxData("-1", "抱歉你的TQ币不足");
        $memberData["TQ"] = intval($memberData["TQ"]) - $info["TQ"];
        $friendData["TQ"] = intval($friendData["TQ"]) + $info["TQ"];
        $this->member->update(array("TQ"=>$memberData["TQ"]), array("userid"=>$info["userid"]));
        $this->member->update(array("TQ"=>$friendData["TQ"]), array("mobile"=>$info["mobile"]));
		
        $this->tqinfo->insert(array("userid"=>$info["userid"], "infotype"=>2, "info"=>$friendData["wechat_name"], "addtime"=>time(), "TQ"=>$info["TQ"]));
        $this->tqinfo->insert(array("userid"=>$friendData["userid"], "infotype"=>3, "info"=>$memberData["wechat_name"], "addtime"=>time(), "TQ"=>$info["TQ"]));
        returnAjaxData("1", "给与成功");
    }
    function tqInfoAjax()
    {
        $neadArg = ["openid"=>[true, 0], "infotype"=>[true, 0], "userid"=>[true, 1]];
        $info = checkArg($neadArg);
        $memberData = $this->checkMember($info); //检测用户是否正确
        $info["infotype"] = explode(",", $info["infotype"]);
        $data = $this->tqinfo->select_or(array("userid"=>[$info["userid"]], "infotype"=>$info["infotype"]), "*", '', "addtime DESC");
        if($data == null)
            returnAjaxData("0", "没有数据");
        $returnData = [];
        $num = 0;
        foreach($data as $row)
        {
            $returnData[$num]["addtime"] = date("Y-m-d H:i:s", $row["addtime"]);
            $returnData[$num]["TQ"] = $row["infotype"]==3 || $row["infotype"]==4?"获得".$row["TQ"]:"失去".$row["TQ"];
            $returnData[$num]["info"] = $row["infotype"] == 3 || $row["infotype"]==4 ?$row["info"].self::$infotype[$row["infotype"]]:self::$infotype[$row["infotype"]].$row["info"];
            $num++;
        }
        returnAjaxData("1", "查询成功",$returnData);
    }
    function sendMobileInfoAjax()
    {
        $neadArg = ["openid"=>[true, 1], "mobile"=>[true, 0], "userid"=>[true, 1]];
        $info = checkArg($neadArg);
        $memberData = $this->checkMember($info); //检测用户是否正确
        if($memberData["mobile"] == $info["mobile"])
            returnAjaxData("-1", "请输入不同手机号");
        include "/api/sms.php";
        returnAjaxData("1", "发送成功");
    }
    function changeMobileAjax()
    {
        $neadArg = ["openid"=>[true, 1], "userid"=>[true, 1], "code"=>[true, 0], "mobile"=>[true, 0]];
        $info = checkArg($neadArg);
        $memberData = $this->checkMember($info); //检测用户是否正确
        $checkInfo = $this->sms_report_db->get_one(array("mobile"=>$info["mobile"]), "mobile,posttime,id_code", "posttime DESC");
        if(time() - intval($checkInfo["posttime"]) > 300)
            returnAjaxData("-1", "验证码超时");
        if($info["code"] != $checkInfo["id_code"])
            returnAjaxData("-1", "验证码错误");
        $this->sms_report_db->update(array("id_code"=>""), array("mobile"=>$info["mobile"]));
        $this->member->update(array("mobile"=>$info["mobile"]), array("userid"=>$info["userid"]));
        returnAjaxData("1", "修改成功");
    }

    function addRecAddress()
    {
        $neadArg = ["openid"=>[true,0], "phone"=>[true, 0], "postcode"=>[true, 0], "district"=>[true, 0], "recvname"=>[true, 0],"address"=>[true,0] ,"userid"=>[true, 1]];
        $info = checkArg($neadArg,"POST");
        $memberData = $this->checkMember($info); //检测用户是否正确
        $info["addtime"] = time();
        unset($info["openid"]);
        $data = $this->zyrecvaddress->get_one(array("userid"=>$info["userid"]));
        if($data == null)
            $info["defaddress"] = 1;
        $this->zyrecvaddress->insert($info);
        returnAjaxData("1", "添加成功");
    }
    function changeDefAdress()//
    {

        $neadArg = ["openid"=>[true, 1], "userid"=>[true, 1], "recvname"=>[false, 0],"phone"=>[false, 0], "postcode"=>[false, 0], "district"=>[false, 0], "address"=>[false, 0], "defaddress"=>[false, 0], "addressID"=>[true, 1]];
        $info = checkArg($neadArg, "POST");
        $memberData = $this->checkMember($info); //检测用户是否正确
        if(isset($info["defaddress"]))
        {
            if($info["defaddress"] == 1)
            {
                $where["userid"] = $info["userid"];
                $where["defaddress"] = 1;
                $this->zyrecvaddress->update(array("defaddress"=>0), $where);
            }

        }
        unset($info["openid"]);
        unset($info["userid"]);
        $addressID["addressID"] = array_pop($info);
        $this->zyrecvaddress->update($info, $addressID);
        returnAjaxData("1", "修改成功");
    }
    function removerAddress()//删除收货地址
    {
        $neadArg = ["openid"=>[true,0], "userid"=>[true,1], "addressID"=>[true, 1]];
        $info = checkArg($neadArg, "POST");
        $memberData = $this->checkMember($info); //检测用户是否正确
        $where["addressID"] = array_pop($info);
        $this->zyrecvaddress->delete($where);
        returnAjaxData("1", "删除成功");
    }
    function getAddress()
    {
        $neadArg = ["userid"=>[true, 1], "openid"=>[true, 0], "addressID"=>[false, 0]];
        $info = checkArg($neadArg, "POST");
        $memberData = $this->checkMember($info); //检测用户是否正确
        unset($info["openid"]);
        $data = $this->zyrecvaddress->select($info, "*", "", "defaddress DESC");
        returnAjaxData("1", "查询成功", $data);
    }
    function getDefAddress()
    {
        $neadArg = ["userid"=>[true, 1], "openid"=>[true, 0]];
        $info = checkArg($neadArg, "POST");
        $memberData = $this->checkMember($info); //检测用户是否正确
        $info["defaddress"] = 1;
        unset($info["openid"]);
        $data = $this->zyrecvaddress->get_one($info, "*");
        returnAjaxData("1", "成功", $data);
    }
	
	function zenyu_title()//
    {
        $info = $this->zenyu->select();

		returnAjaxData("1", "成功", $info);
        
    }
		

	

}