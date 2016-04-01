<?php
namespace Home\Api;

use Think\Controller;

class ExecMin1Api extends BaseApi
{

    public function _initialize()
    {
        parent::_initialize();
        $this->mExecList = array(
            //深渊之战
            'AbyssBattle.reborn',//查看是否有Boss复活
        );

         $this->mLogList = array(
            'Linekong' => array(//蓝港eRating
//                'queue',//发送队列消息
                'logout',//发送登出消息
                'online',//发送在线人数
            ),
        );

    }

    public function index()
    {
        return $this->execute();
    }

}