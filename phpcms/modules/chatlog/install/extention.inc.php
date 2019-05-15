<?php
// +------------------------------------------------------------
// | distribution
// +------------------------------------------------------------
// | 卓远网络：CY QQ:185017580 http://www.300c.cn/
// +------------------------------------------------------------
// | 欢迎加入卓远网络-Team，和卓远一起，精通PHPCMS
// +------------------------------------------------------------
// | 版本号：20180208
// +------------------------------------------------------------
defined('IN_PHPCMS') or exit('Access Denied');
defined('INSTALL') or exit('Access Denied');


//先判断有没有运费模板的大菜单
$zywldb = $menu_db->get_one(array('name'=>'聊天','parentid'=>'0'));
if($zywldb){
	$parentid =$zywldb['id'];
}else{
	$parentid = $menu_db->insert(
	array(
		'name'=>'聊天',
		'parentid'=>'0',
		'm'=>'chatlog',
		'c'=>'chatlog',
		'a'=>'init',
		'data'=>'',
		'listorder'=>10,
		'display'=>'1'
		),
	true
    );
}

/**
 * 添加菜单:运费模板管理
 */
$pid = $menu_db->insert(
	array(
		'name'=>'聊天模板管理', //菜单名称
		'parentid'=>$parentid, //添加到后台的主菜单里
		'm'=>'chatlog', //模块
		'c'=>'chatlog', //文件
		'a'=>'init',//方法
		'data'=>'', //附加参数
		'listorder'=>0, //菜单排序
		'display'=>'1'), //显示菜单 1是显示 0是隐藏
		true //插入菜单之后，是否返回id
	);

/**
 * 添加子菜单  商品列表
 */
$menu_db->insert(
	array(
		'name'=>'聊天模板列表', //菜单名称
		'parentid'=>$pid, //添加到积分商城。
		'm'=>'chatlog', //模块
		'c'=>'chatlog',//文件 
		'a'=>'chatloglist', //方法
		'data'=>'', //附加参数
		'listorder'=>1, //菜单排序
		'display'=>'1' //显示菜单 1是显示 0是隐藏
		)
	);
?>