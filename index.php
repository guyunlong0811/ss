<?php
//导入配置文件
require_once('config.php');

//入口
if(!isset($_GET['m']))$_GET['m'] = 'Home'; // 绑定Home模块到当前入口文件
if(!isset($_GET['c'])){
	$_GET['c'] = 'Router'; // 绑定Router模块到当前入口文件
	$_GET['a'] = 'request'; // 绑定request模块到当前入口文件
}

//导入TP框架
require_once('ThinkPHP/ThinkPHP.php');