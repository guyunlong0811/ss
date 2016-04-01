<?php
namespace Home\Api;

use Think\Controller;

class RankApi extends BaseApi
{

    //战队
    public function level()
    {
        $list = D('GTeam')->getRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'team', $jsonRank);
        return true;
    }

    //VIP
    public function vip()
    {
        $list = D('GVip')->getRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'vip', $jsonRank);
        return true;
    }

    //副本星数
    public function star()
    {
        $list = D('GCount')->getStarRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'star', $jsonRank);
        return true;
    }

    //竞技场排名
    public function arena()
    {

        //获取排名奖励配置
        $rankConfig = D('Static')->access('rank_bonus', 1);

        //计算需要的最大排名
        $end = end($rankConfig);
        $max = $end['rank_end'];

        //排名
        $rankList = D('GArena')->rank($max);

        //创建邮件
        $mailList = array();
        foreach ($rankConfig as $config) {
            foreach ($rankList as $key => $data) {
                if ($config['rank_start'] <= $data['rank'] && $data['rank'] <= $config['rank_end']) {
                    $mail = array();
                    $mail['mail_id'] = $config['rank_mail'];
                    $mail['tid'] = 0;
                    $mail['target_tid'] = $data['tid'];
                    $mail['params']['target_nickname'] = $data['nickname'];
                    $mail['params']['rank'] = $data['rank'];
                    $mailList[] = $mail;
                    unset($rankList[$key]);
                } else {
                    break;
                }
            }
        }

        //返回
        if (!empty($mailList)) {
            if (false === D('GMail')->sendAll($mailList)) {
                return false;
            }
        }

        return true;
    }

    //竞技场连胜
    public function arenaWinContinuous()
    {
        $list = D('GCount')->getArenaWinContinuousRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'arena_win_continuous', $jsonRank);
        return true;
    }

    //公会排行榜
    public function league()
    {
        if (false === D('TDailyLeague')->TruncateTable('t_daily_league')) {
            return false;
        }
        $list = D('GLeague')->getList();
        if (!empty($list) && false === D('TDailyLeague')->CreateAllData($list)) {
            return false;
        }

        //放入redis
        $list = D('TDailyLeague')->getList(0, C('RANK_MAX'));
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'league', $jsonRank);
        return true;
    }

    //连击排行榜
    public function combo()
    {
        $list = D('GCount')->getComboRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'combo', $jsonRank);
        return true;
    }

    //连击排行榜
    public function comboToday()
    {
        $list = D('TDailyCount')->getComboRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'combo_today', $jsonRank);
        return true;
    }

    //战力排行榜
    public function force()
    {
        $list = D('GCount')->getForceRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'force', $jsonRank);
        return true;
    }

    //最强小队战力排行榜
    public function forceTop()
    {
        $list = D('GCount')->getForceTopRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'force_top', $jsonRank);
        return true;
    }

    //连击排行榜发放奖励
    public function comboTodayBonus()
    {

        //获取排名奖励配置
        $rankConfig = D('Static')->access('rank_bonus', 2);

        //计算需要的最大排名
        $end = end($rankConfig);
        $max = $end['rank_end'];

        //排名
        $rankList = D('TDailyCount')->getComboRankList($max);

        //创建邮件
        $mailList = array();
        foreach ($rankConfig as $value) {
            $i = $value['rank_start'];
            while ($i <= $value['rank_end']) {
                if (isset($rankList[($i - 1)])) {
                    $mail = array();
                    $mail['mail_id'] = $value['rank_mail'];
                    $mail['tid'] = 0;
                    $mail['target_tid'] = $rankList[($i - 1)]['tid'];
                    $mail['params']['target_nickname'] = $rankList[($i - 1)]['nickname'];
                    $mail['params']['rank'] = $i;
                    $mail['params']['combo'] = $rankList[($i - 1)]['data'];
                    $mailList[] = $mail;
                } else {
                    break;
                }
                ++$i;
            }

        }

        //返回
        if (!empty($mailList)) {
            if (false === D('GMail')->sendAll($mailList)) {
                return false;
            }
        }

        return true;

    }

    //成就点排行榜
    public function achievement()
    {
        $list = D('GCount')->getAchievementRankList();
        $jsonRank = json_encode($list);
        D('Predis')->cli('game')->hset('rank', 'achievement', $jsonRank);
        return true;
    }

}