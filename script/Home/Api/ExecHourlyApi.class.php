<?php
namespace Home\Api;

use Think\Controller;

class ExecHourlyApi extends BaseApi
{

    public function _initialize()
    {
        parent::_initialize();
        $this->mExecList = array(
            //竞技场
            'Arena.rank',//排名奖励

            //公会
            'League.activitySync',//同步活跃度

            //错误检查
            'Check.daily',//每日
        );
    }

    public function index()
    {
        return $this->execute();
    }

}