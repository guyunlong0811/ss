<?php
namespace Home\Api;

use Think\Controller;

class LeagueBattleApi extends BaseApi
{

    const GROUP = 7;

    //公会奖励
    public function leagueReward()
    {

        //设置行为
        C('G_BEHAVE', 'league_battle_advance_bonus');

        $eventList = D('GEvent')->getGroup(self::GROUP);
        if (!empty($eventList)) {

            foreach ($eventList as $value) {

                if ($value['status'] != '2') continue;

                //解析数据
                $ps = json_decode($value['ps'], true);

                //获取总据点数
                $battleConfig = D('Static')->access('instance_info_map', $ps['map']);
                $count = count($battleConfig);

                //获取各个公会攻占的据点
                $leagueBattleList = D('GLeague')->battleComplete();
//            dump($leagueBattleList);
                $mail_id = D('Static')->access('params', 'LEAGUE_AREA_NOTICE');
                foreach ($leagueBattleList as $val) {

                    //计算完成率
                    $rate = round(($val['count'] / $count) * 100);
                    if ($rate > 0) {

                        //获取奖励资金
                        $fund = lua('league_area_battle', 'league_battle_leaguereward', array($rate));

                        //加公会资金
                        if ($fund > 0) {
                            D('GLeague')->incAttr($val['id'], 'fund', $fund);

                            //发送邮件
                            $mail = array();
                            $mail['mail_id'] = $mail_id;
                            $mail['tid'] = 0;
                            $mail['target_tid'] = $value['president_tid'];
                            $mail['params']['fund'] = $fund;
                            $mailList[] = $mail;
                        }

                    }

                }

                //返回
                if (!empty($mailList)) {
                    if (false === D('GMail')->sendAll($mailList)) {
                        return false;
                    }
                }

                //清除记录
                $update['id'] = $value['id'];
                $update['status'] = '0';
                $update['ps'] = '';
                if (false === D('GEvent')->UpdateData($update)) {
                    return false;
                }

            }

        }

        //清除记录表
        return true;
    }

    //每日奖励获胜公会成员
    public function leagueMemberReward()
    {

        //现在时间
        $now = time();

        //获取获胜公会信息
        $leagueInfo = M('LLeagueBattleResult')->order('`ctime` DESC')->getField('league_id');

        //获取公会成员
        $memberList = D('GLeagueTeam')->getALLTid($leagueInfo['id']);
        $mail_id = D('Static')->access('params', 'LEAGUE_AREA_WIN_PLAYER');
        foreach ($memberList as $value) {
            $mail = array();
            $mail['mail_id'] = $mail_id;
            $mail['tid'] = 0;
            $mail['target_tid'] = $value;
            $mail['params']['leaguename'] = $leagueInfo['name'];
            $mailList[] = $mail;
        }

        //返回
        if (!empty($mailList)) {
            if (false === D('GMail')->sendAll($mailList)) {
                return false;
            }
        }
        return true;

    }

}