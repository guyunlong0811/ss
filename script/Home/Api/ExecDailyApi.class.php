<?php
namespace Home\Api;

use Think\Controller;

class ExecDailyApi extends BaseApi
{

    public function _initialize()
    {
        parent::_initialize();
        $this->mExecList = array(

            //公会
//            'LeagueBattle.leagueReward',//公会战公会奖励
//            'LeagueBattle.leagueMemberReward',//公会战成员奖励

            //PVE公会战
            'LeagueFight.account',//PVE公会战结算

            //PVP公会战
            'LeagueArena.account',//PVP公会战结算
            'LeagueArena.accountTop',//PVP公会战第一名结算
            'LeagueArena.bonus',//PVP公会战榜首奖励

            //VIP
            'Vip.bonus',//VIP每日奖励

            //排名
            'Rank.level',//等级榜
            'Rank.vip',//VIP等级榜
            'Rank.star',//副本星数
//            'Rank.arena',//竞技场
            'Rank.arenaWinContinuous',//竞技场连胜
            'Rank.league',//公会排行榜
            'Rank.combo',//连击排行榜
            'Rank.force',//战力排行榜
            'Rank.forceTop',//最强小队战力排行榜
            'Rank.comboTodayBonus',//当日连击排行榜奖励发放
            'Rank.achievement',//成就点排行榜

            //祈愿
            'Pray.timed',//限时祈愿

            //订单
            'Pay.clean',//归档清除过期订单

            //日志
            'Log.backup',//备份当天日志
            'Log.clean',//清除多余记录

            //每日登录活动
            'DailyRegister.clean',//清除每日登录活动表

            //清空
            'Truncate.daily',//每日清空
            'Truncate.weekly',//每周清空

            //邮件
            'Mail.clean',//清除过期邮件

            //好友
//            'Friend.clean',//好友体力赠送记录

        );

    }

    public function index()
    {
        //查看是否已经执行过
        $dateApc = D('Predis')->cli('game')->get('ExecDaily_S' . C('G_SID'));
        $date = time2format(null, 2);
        if ($dateApc == $date) {
            return true;
        }
        D('Predis')->cli('game')->set('ExecDaily_S' . C('G_SID'), $date);

        //新建文件
        create_sql('', COMMON_PATH . 'Common/db/S' . C('G_SID') . '/' . date('Ymd') . '/', 'daily.sql', 'w');

        //修改内存占用量
        ini_set('memory_limit', '2048M');

        //执行
        $rs1 = $this->execute();

        //数据库
        $sid = C('G_SID');
        $serverList = get_server_list();

        //修改文件名
        $newFile = time() . '.sql';
        $exec = 'mv ' . $this->mDBPath . $this->mFile . ' ' . $this->mDBPath . $newFile;
        exec($exec);

        //执行
        $exec = 'mysql -h' . $serverList[$sid]['master']['DB_HOST'] . ' -u' . $serverList[$sid]['master']['DB_USER'] . " -p'" . $serverList[$sid]['master']['DB_PWD'] . "' " . $serverList[$sid]['dbname'] . ' < ' . $this->mDBPath . $newFile . ' --default-character-set=utf8';
        if(false === exec($exec)){
            $rs2 = false;
        }else{
            $rs2 = true;
        }

        //返回
        return $rs1 && $rs2;

    }

}