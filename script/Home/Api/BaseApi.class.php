<?php
namespace Home\Api;

use Think\Controller;

class BaseApi extends Controller
{

    protected $mTransFlag;//事物标识

    protected $mExecList = array();//执行列表
    protected $mErrorList = array();//错误列表

    protected $mLogTableList = array(
        'l_abyss',
        'l_activity_complete',
        'l_arena',
        'l_arena_battle',
        'l_cheat',
        'l_dynamic',
        'l_emblem',
        'l_equip_strengthen',
        'l_equip_upgrade',
        'l_iap',
        'l_instance',
        'l_item',
        'l_league',
        'l_league_battle',
        'l_league_battle_result',
        'l_league_boss',
        'l_league_dismiss',
        'l_league_donate',
        'l_league_fight',
        'l_league_food',
        'l_league_team',
        'l_league_team_member',
        'l_login',
        'l_mail',
        'l_member',
        'l_order',
        'l_partner',
        'l_pray',
        'l_share',
        'l_shop',
        'l_star',
        'l_team',
        'l_vip',
    );

    protected $mTruncateTableList = array(
        't_daily_activity_bonus',
        't_daily_count',
        't_daily_event',
        't_daily_instance',
        't_daily_league',
        't_daily_online_bonus',
        't_daily_quest',
        't_daily_shop',
        't_specify_event',
        't_weekly_event',
    );

    protected $mDBPath = '';
    protected $mFile = '';

    public function _initialize()
    {
        set_time_limit(0);

        //sql文件信息
        $this->mDBPath = COMMON_PATH . 'Common/db/S' . C('G_SID') . '/' . date('Ymd') . '/';
        $this->mFile = 'daily.sql';
    }

    //开始事务
    protected function transBegin()
    {
        C('G_TRANS', true);//事务标示
        $this->mTransFlag = false;
        M()->startTrans();
    }

    //结束事务
    protected function transEnd()
    {

        if (!$this->mTransFlag) {
            M()->rollback();
            if (C('G_ERROR') != 'db_error')//如果不是数据库有问题
                C('G_TRANS', false);//结束事务
        } else {
            M()->commit();
            C('G_TRANS', false);//结束事务
        }
        return $this->mTransFlag;

    }

    //执行
    protected function execute()
    {
        $this->execBatch($this->mExecList);
        if (!empty($this->mErrorList)) {
            C('G_ERROR_EXEC', $this->mErrorList);
            return false;
        }
        return true;
    }

    //结算
    protected function execBatch($list)
    {
        if (!empty($list)) {
            foreach ($list as $value) {
                $arr = explode('.', $value);
                $c = $arr[0];
                $a = $arr[1];
                $this->transBegin();
                if (false === A($c, 'Api')->$a()) {
                    $this->mErrorList[] = $value;
                } else {
                    $this->mTransFlag = true;
                }
                $this->transEnd();
            }
        }
        return;
    }

    //查询活动是否在当日开放
    protected function isOpenToday($group)
    {
        //查询已开放的活动
        $eventEnable = D('GParams')->getValue('ENABLE_EVENT');
        $eventEnable = json_decode($eventEnable, true);

        //查看是否到了匹配时间
        $now = time();
        $w = date('N', $now);
        $dt = time2format($now, 2);
        $eventConfig = D('Static')->access('event', $group);
        foreach ($eventConfig as $value) {
            if(!in_array($value['index'], $eventEnable)){
                continue;
            }else if ($value['type'] == '0') {
                continue;
            }else if ($value['type'] == '2' && $value['start_date'] != $w) {
                continue;
            }else if ($value['type'] == '3' && $value['start_date'] != $dt) {
                continue;
            }else{
                return $value['start_time'];
            }
        }
        return false;

    }

    //查询活动是否在当日结束
    protected function isOverToday($group)
    {
        //查询已开放的活动
        $eventEnable = D('GParams')->getValue('ENABLE_EVENT');
        $eventEnable = json_decode($eventEnable, true);

        //查看是否到了匹配时间
        $now = strtotime("-1 days");
        $w = date('N', $now);
        $dt = time2format($now, 2);
        $eventConfig = D('Static')->access('event', $group);
        foreach ($eventConfig as $value) {
            if(!in_array($value['index'], $eventEnable)){
                continue;
            }else if ($value['type'] == '0') {
                continue;
            }else if ($value['type'] == '2' && $value['end_date'] != $w) {
                continue;
            }else if ($value['type'] == '3' && $value['end_date'] != $dt) {
                continue;
            }else{
                return true;
            }
        }

        return false;

    }

    //活动是否启用
    protected function isEventEnable($group){
        $eventEnable = D('GParams')->getValue('ENABLE_EVENT');
        $eventEnable = json_decode($eventEnable, true);
        foreach($eventEnable as $value){
            if(floor($value / 100) == $group){
                return true;
            }
        }
        return false;
    }

}