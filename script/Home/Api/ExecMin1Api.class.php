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
    }

    public function index()
    {
        return $this->execute();
    }

}