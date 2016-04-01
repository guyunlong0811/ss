<?php
$protocol = require_once('protocol.php');
$behave = require_once('behave.php');
$ident = require_once('ident.php');
$config = array(

    //数据库部分
    'DB_TYPE' => 'mysql',
    'DB_PREFIX' => '',                  //数据库表名前缀
    'DB_CHARSET' => 'utf8',             //数据库字符类型
    'DB_FIELDS_CACHE' => true,            // 禁用字段缓存(不同库中有相同名字的表)

    //缓存
    'DATA_CACHE_TIME' => 0,// 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_TYPE' => 'Apc',

    //Redis缓存
    'REDIS_TOKEN_TIME' => 1800,//用户登录凭证有效时间（秒）
    'REDIS_CHAT_WORLD_TIME' => 86400,//世界频道留言保存时间
    'REDIS_CHAT_LEAGUE_TIME' => 86400,//公会频道留言保存时间
    'REDIS_CHAT_NOTICE_TIME' => 600,//跑马灯保存时间
    'REDIS_CHAT_LEAGUE_NOTICE_TIME' => 300,//公会战公告保存时间
    'REDIS_CHAT_WORLD_ROW' => 20,//世界频道每页显示聊天数
    'REDIS_CHAT_LEAGUE_ROW' => 20,//公会频道每页显示聊天数
    'REDIS_CHAT_NOTICE_ROW' => 20,//公告一次最多收取条数

    //日志
    'LOG_TYPE' => 'File',//日志记录类型
    'LOG_RECORD' => true,//开启了日志记录
//    'LOG_LEVEL'  =>'DEBUG,EMERG,ALERT,CRIT,ERR',

    'GAME_ID' => 1,//游戏编号
    'G_SID' => 0,//服务器编号

    'LUA_URL' => './lua/',//LUA文件路径

    'DAILY_UTIME' => '03:00:00',
    'SALT' => '4everGame@565420',

    'UC_VERIFY' => array(//用户中心配置
        'time_limit' => 600,//时间容错率
        'request' => 'forever!23',
        'respond' => 'forever!23',
    ),

    'CACHE_VERIFY' => 'fg_otl_cache',//清除缓存

    'RANK_MAX' => 50,//排行榜最低名次

    'APC_PREFIX' => 'script_',

//    'LEAGUE_BATTLE_PROTECT_TIME' => 360,//公会活动战斗保护时间

    //全局变量
    'G_BEHAVE' => '',//当前协议的行为代号
    'G_REDIS' => false,//REDIS是否有问题(false为没有问题，否则则为出问题的库号)
    'G_STATIC' => false,//静态表是否问题(false为没有问题，否则则为出问题的表名)
    'G_TRANS' => false,//是否启用了事务
    'G_ERROR' => null,//错误提示
    'G_SQL' => array(),//trans过程中的所有SQL
    'G_SQL_ERROR' => array(),//所有报错的SQL

    //邮件配置
    'THINK_EMAIL' => array(
        'SMTP_HOST' => 'smtp.qq.com', //SMTP服务器
        'SMTP_PORT' => '25', //SMTP服务器端口
        'SMTP_USER' => 'error@forevergame.com', //SMTP服务器用户名
        'SMTP_PASS' => 'acWwDKiINOcH4!a', //SMTP服务器密码
        'FROM_EMAIL' => 'error@forevergame.com', //发件人EMAIL
        'FROM_NAME' => 'SS', //发件人名称
        'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
        'REPLY_NAME' => '', //回复名称（留空则为发件人名称）
    ),

    'WARNING_TYPE' => 'Mail',

);
return array_merge($config, $ident, $protocol, $behave);
