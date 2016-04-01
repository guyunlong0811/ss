<?php
namespace Home\Api;

use Think\Controller;

class ExecMin10Api extends BaseApi
{

    public function _initialize()
    {
        parent::_initialize();
        $this->mExecList = array(
            //活动
            'Event.check',//检查活动当前状态是否有改变
            //商店
            'Shop.open',//检查是否有商店开启
            'Shop.clean',//归档过期商店
            //排名
            'Rank.comboToday',//每日combo排名
            //PVE公会战
            'LeagueFight.match',//PVE公会战匹配
            //PVP公会战
            'LeagueArena.match',//PVP公会战匹配
        );
    }

    public function index()
    {
        return $this->execute();
    }

}