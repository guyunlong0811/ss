<?php
namespace Home\Api;

use Think\Controller;

class ExecOpenApi extends BaseApi
{

    public function _initialize()
    {
        $this->mExecList = array(
            //活动
            'Event.check',//检查活动当前状态是否有改变
        );
    }

    public function index()
    {
        return $this->execute();
    }

}