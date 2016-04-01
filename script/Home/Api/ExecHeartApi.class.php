<?php
namespace Home\Api;

use Think\Controller;

class ExecHeartApi extends BaseApi
{

    public function _initialize()
    {
        $this->mExecList = array();
    }

    public function index()
    {
        return $this->execute();
    }

}