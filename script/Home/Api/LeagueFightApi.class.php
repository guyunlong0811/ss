<?php
namespace Home\Api;

use Think\Controller;

class LeagueFightApi extends BaseApi
{

    const GROUP = 15;

    //公会匹配
    public function match()
    {
        //活动是否开启
        if (!$this->isEventEnable(self::GROUP)) {
            return true;
        }

        //查看今天是否已经匹配完成
        if (D('Predis')->cli('fight')->exists('lf:end')) {
            return true;
        }

        //查看今天是否有活动
        $now = time();
        if (false === $starttime = $this->isOpenToday(self::GROUP)) {
            return true;
        }

        //是否到了匹配时间
        $openTime = strtotime(time2format(null, 2) . ' ' . $starttime);
        $matchTime = $openTime - 3660;
        if ($matchTime > $now || $now > $openTime) {
            return true;
        }

        //清空公会战信息
        $this->clearRedis();

        //不允许公会解散
        D('Predis')->cli('game')->set('league_dismiss_status', '0');

        //计算最后积分(lua)
        $field = array('id', 'name', 'point', 'record', 'president_tid', 'center_level', 'shop_level', 'food_level', 'attribute_level', 'boss_level');
        $leagueList = D('GLeague')->field($field)->select();
        foreach ($leagueList as $key => $value) {
            $modified_point = lua('league_battle', 'league_battle_match', array($value['point'], $value['record'], $value['center_level'], $value['shop_level'], $value['food_level'], $value['attribute_level'], $value['boss_level']));
            $leagueList[$key]['modified_point'] = $modified_point + $value['center_level'];
        }

        //根据修正积分排序->field('league_id')
        $leagueList = arr_field_sort($leagueList, 'modified_point', 'desc');

        //获取最新公会战排名
        $leagueIdRankList = D('GLeagueRank')->order("`point` DESC,`center_level` DESC,`count` DESC,`league_id` ASC")->getField('league_id', true);
        $rank = array();
        foreach ($leagueIdRankList as $key => $value) {
            $rank[$value] = $key + 1;
        }

        //匹配
        $i = 1;
        $leagueInfo = array();
        $leagueConfig1 = array();
        foreach ($leagueList as $value) {

            //单数
            if ($i % 2 == 1) {
                $leagueInfo = $value;
                $leagueConfig1 = D('Static')->access('league', $value['center_level']);
            } else {//双数

                //计算分组
                $group = $i / 2;

                //单数公会

                //我方公会信息
                $add1['group'] = $group;
                $add1['league_id'] = $leagueInfo['id'];
                $add1['league_name'] = $leagueInfo['name'];
                $add1['league_rank'] = $rank[$leagueInfo['id']] ? $rank[$leagueInfo['id']] : 0;
                $add1['league_point'] = $leagueInfo['point'];
                $add1['league_level'] = $leagueInfo['center_level'];
                $add1['league_count'] = D('GLeagueTeam')->getMemberCount($leagueInfo['id']);
                $add1['president_tid'] = $leagueInfo['president_tid'];

                //敌方信息
                $add1['target_league_id'] = $value['id'];
                $add1['target_league_name'] = $value['name'];
                $add1['target_league_rank'] = $rank[$value['id']] ? $rank[$value['id']] : 0;
                $add1['target_league_point'] = $value['point'];
                $add1['target_league_level'] = $value['center_level'];

                //相对我方战斗结果
                $add1['result'] = 2;

                //我放战场信息
                $add1['buff1'] = 0;//公会锦囊buff
                $add1['buff2'] = 0;//公会锦囊buff
                $add1['assault'] = 0;//公会突击次数
                $add1['occupied'] = 0;//已占领个数
                for ($j = 1; $j <= 3; ++$j) {
                    $add1['h:' . $j . ':ins'] = $leagueConfig1['stronghold_' . $j . '_instance'];
                    $add1['h:' . $j . ':hp'] = $leagueConfig1['stronghold_' . $j . '_hp'];
                    $add1['h:' . $j . ':hp'] = lua('league_battle', 'league_battle_monster_hp', array($j, $leagueInfo['center_level'], $leagueInfo['shop_level'], $leagueInfo['food_level'], $leagueInfo['attribute_level'], $leagueInfo['boss_level'],));
                    $add1['h:' . $j . ':damage'] = 0;
                    $add1['h:' . $j . ':kill'] = 0;
                }

                //双数公会
                $leagueConfig2 = D('Static')->access('league', $value['center_level']);

                //我方公会信息
                $add2['group'] = $group;
                $add2['league_id'] = $value['id'];
                $add2['league_name'] = $value['name'];
                $add2['league_rank'] = $rank[$value['id']] ? $rank[$value['id']] : 0;
                $add2['league_point'] = $value['point'];
                $add2['league_level'] = $value['center_level'];
                $add2['league_count'] = D('GLeagueTeam')->getMemberCount($value['id']);
                $add2['president_tid'] = $value['president_tid'];

                //敌方信息
                $add2['target_league_id'] = $leagueInfo['id'];
                $add2['target_league_name'] = $leagueInfo['name'];
                $add2['target_league_rank'] = $rank[$leagueInfo['id']] ? $rank[$leagueInfo['id']] : 0;
                $add2['target_league_point'] = $leagueInfo['point'];
                $add2['target_league_level'] = $leagueInfo['center_level'];

                //相对我方战斗结果
                $add2['result'] = 2;

                //我放战场信息
                $add2['buff1'] = 0;//公会锦囊buff
                $add2['buff2'] = 0;//公会锦囊buff
                $add2['assault'] = 0;//公会突击次数
                $add2['occupied'] = 0;//已占领个数
                for ($j = 1; $j <= 3; ++$j) {
                    $add2['h:' . $j . ':ins'] = $leagueConfig2['stronghold_' . $j . '_instance'];
                    $add2['h:' . $j . ':hp'] = lua('league_battle', 'league_battle_monster_hp', array($j, $value['center_level'], $value['shop_level'], $value['food_level'], $value['attribute_level'], $value['boss_level'],));
                    $add2['h:' . $j . ':damage'] = 0;
                    $add2['h:' . $j . ':kill'] = 0;
                }

                //数据
                if (false === D('Predis')->cli('fight')->hmset('lf:l:' . $add1['league_id'], $add1)) {
                    return false;
                }
                if (false === D('Predis')->cli('fight')->hmset('lf:l:' . $add2['league_id'], $add2)) {
                    return false;
                }

                //战斗结果
                if (false === D('Predis')->cli('fight')->hset('lf:end', 'g' . $group, 0)) {
                    return false;
                }

                //清空信息
                $leagueInfo = array();
                $add1 = array();
                $add2 = array();

            }

            ++$i;

        }

        //轮空公会直接判胜
        if (!empty($leagueInfo)) {
            $add['league_id'] = $leagueInfo['id'];
            $add['league_name'] = $leagueInfo['name'];
            $add['league_point'] = $leagueInfo['point'];
            $add['center_level'] = $leagueInfo['center_level'];
            $add['league_count'] = D('GLeagueTeam')->getMemberCount($leagueInfo['id']);
            $add['president_tid'] = $leagueInfo['president_tid'];
            $add['target_league_id'] = 0;
            $add['target_league_name'] = '';
            $add['target_league_point'] = 0;
            $add['target_league_level'] = $leagueInfo['center_level'];
            $add['result'] = 1;
            if (false === D('Predis')->cli('fight')->hmset('lf:l:' . $add['league_id'], $add)) {
                return false;
            }
        }

        return true;

    }

    //公会战结算奖励
    public function account()
    {

        //查看今天是否已经匹配完成
        if (!D('Predis')->cli('fight')->exists('lf:end')) {
            return true;
        }

//        //活动是否开启
//        if (!$this->isEventEnable(self::GROUP)) {
//            return true;
//        }
//
//        //查看昨天有没有活动开启
//        if (false === $this->isOverToday(self::GROUP)) {
//            return true;
//        }

        $now = time();
        C('G_BEHAVE', 'league_fight_rank');
        $behave = get_config('BEHAVE', array('league_fight_rank', 'code'));

        //获取所有参加公会战战队ID
        $keysList = D('Predis')->cli('fight')->keys('lf:t:*');
        if (empty($keysList)) {
            return true;
        }

        $join = array();
        foreach ($keysList as $value) {
            $count = D('Predis')->cli('fight')->hget($value, 'fight_count');
            if ($count > 0) {
                $arr = explode(':', $value);
                $join[] = $arr[2];
            }
        }

        //查询公会战结果
        $fightList = D('Predis')->cli('fight')->keys('lf:l:*');

        //查询所有公会信息
        $select = D('GLeague')->field('`id`,`fund`,`point`,`record`')->select();
        $LeagueList = arr_kv($select, 'id');
        $leagueIdList = array_keys($LeagueList);
        unset($select);

        //日志统一insert
        $insertLLeagueBase = "insert into `l_league` (`id`,`league_id`,`attr`,`value`,`before`,`after`,`behave`,`ctime`) values ";
        $sqlLLeague = '';

        $insertLLeagueFightBase = "insert into `l_league_fight` (`id`,`league_id`,`target_league_id`,`hold`,`result`,`ctime`) values ";
        $sqlLLeagueFight = '';

        //获取公会资金奖励
        $leagueReward = array();
        for ($i = 0; $i <= 3; ++$i) {
            $leagueReward[$i] = D('Static')->access('params', 'LEAGUE_BATTLE_REWARD_GOLD_' . $i);
        }

        //发放占领对方据点公会资金奖励
        $fightLeagueIdList = array();
        $fightLeagueResult = array();
        $mailList = array();
        $updateGLeagueBase = "update `g_league` set ";
        foreach ($fightList as $value) {

            //获取敌我公会信息
            $leagueInfo = D('Predis')->cli('fight')->hgetall($value);
            $targetTotalHP = 0;
            $targetTotalDamage = 0;
            $damageRate = 0;
            $targetLeagueInfo = array();
            if ($leagueInfo['target_league_id'] > 0) {
                $targetLeagueInfo = D('Predis')->cli('fight')->hgetall('lf:l:' . $leagueInfo['target_league_id']);
                //计算对手公会的血量以及我方伤害
                for ($i = 1; $i <= 3; ++$i) {
                    $targetTotalHP += $targetLeagueInfo['h:' . $i . ':hp'];
                    $targetTotalDamage += $targetLeagueInfo['h:' . $i . ':damage'];
                    $damageRate = floor($targetTotalDamage / $targetTotalHP * 10000);
                    $damageRate = $damageRate > 10000 ? 10000 : $damageRate;
                }
            }

            //公会已经解散则跳过
            if (!in_array($leagueInfo['league_id'], $leagueIdList)) {
                continue;
            }

            //参加公会战公会ID列表
            $fightLeagueIdList[] = $leagueInfo['league_id'];

            //公会战结果列表
            $fightLeagueResult[$leagueInfo['league_id']] = $leagueInfo['result'];

            //计算公会最新分数
            $point = lua('league_battle', 'league_battle_points', array((int)$leagueInfo['result'], (int)$leagueInfo['league_point'], (int)$leagueInfo['target_league_point'], (int)$damageRate));

            //修改战绩
            $newRecord = substr($LeagueList[$leagueInfo['league_id']]['record'], -4) . $leagueInfo['result'];
            $sqlSet = "`record`='{$newRecord}'";
            $sqlLLeague .= "(null,'{$leagueInfo['league_id']}','record','{$newRecord}','{$LeagueList[$leagueInfo['league_id']]['record']}','{$newRecord}','{$behave}','{$now}'),";

            //修改积分
            if ($point != 0) {
                $newPoint = $LeagueList[$leagueInfo['league_id']]['point'] + $point;
                $sqlSet .= ",`point`=`point` + '{$point}'";
                $sqlLLeague .= "(null,'{$leagueInfo['league_id']}','point','{$point}','{$LeagueList[$leagueInfo['league_id']]['point']}','{$newPoint}','{$behave}','{$now}'),";
            }

            //计算据点占领数量
            if ($leagueInfo['result'] == 1) {
                $num = 3;
            } else {
                $num = 0;
                if (!empty($targetLeagueInfo)) {
                    for ($i = 1; $i <= 3; ++$i) {
                        if ($targetLeagueInfo['h:' . $i . ':kill'] > 0) {
                            ++$num;
                        }
                    }
                }
            }

            //加公会资金
            if ($leagueReward[$num] > 0) {
                $newFund = $LeagueList[$leagueInfo['league_id']]['fund'] + $leagueReward[$num];
                $sqlSet .= ",`fund`=`fund` + '{$leagueReward[$num]}'";
                $sqlLLeague .= "(null,'{$leagueInfo['league_id']}','fund','{$leagueReward[$num]}','{$LeagueList[$leagueInfo['league_id']]['fund']}','{$newFund}','{$behave}','{$now}'),";
            }

            //生成日志
            $sqlLLeagueFight .= "(null,'{$leagueInfo['league_id']}','{$leagueInfo['target_league_id']}','{$num}','{$leagueInfo['result']}','{$now}'),";

            $sqlGLeague = $updateGLeagueBase . $sqlSet . " where `id`='{$leagueInfo['league_id']}' limit 1;";
            M('GLeague')->execute($sqlGLeague);
//            create_sql($sqlGLeague, $this->mDBPath, $this->mFile);

            //生成邮件数据
            $mail = array();
            $mail['mail_id'] = 2300 + $num;
            $mail['tid'] = 0;
            $mail['target_tid'] = $leagueInfo['president_tid'];
            $mail['params']['target_league_name'] = $leagueInfo['target_league_name'];
            $mailList[] = $mail;
        }

        //公会属性改变日志
        if(false !== $sql = insert_implode($insertLLeagueBase, $sqlLLeague)){
            create_sql($sql, $this->mDBPath, $this->mFile);
        }

        //公会战结果日志
        if(false !== $sql = insert_implode($insertLLeagueFightBase, $sqlLLeagueFight)){
            create_sql($sql, $this->mDBPath, $this->mFile);
        }

        //公会排名
        $list = D('GLeague')->rank();
        if (false === D('GLeagueRank')->TruncateTable('g_league_rank')) {
            return false;
        }
        if (false === D('GLeagueRank')->CreateAllData($list)) {
            return false;
        }

        //将公会排名存入redis
        $leagueRank = D('GLeagueRank')->getList(0, C('RANK_MAX'));
        $jsonRank = json_encode($leagueRank);
        D('Predis')->cli('game')->hset('rank', 'league_fight', $jsonRank);

        //整理数据
        $i = 1;
        $leagueRankList = array();
        foreach ($leagueRank as $value) {
            $leagueRankList[$value['league_id']] = $i;
            ++$i;
        }

        //获取所有公会成员
        $leagueTeamList = M('GLeagueTeam')->field('`tid`,`league_id`')->select();
        foreach ($leagueTeamList as $value) {
            if (!in_array($value['league_id'], $fightLeagueIdList)) {
                continue;
            }
            $mail = array();
            $mail['mail_id'] = lua('league_battle', 'league_battle_reward_mail', array($leagueRankList[$value['league_id']], $fightLeagueResult[$value['league_id']]));
            $mail['tid'] = 0;
            $mail['target_tid'] = $value['tid'];
            $mail['params']['league_rank'] = $leagueRankList[$value['league_id']];
            $mailList[] = $mail;
            //胜利
            if ($fightLeagueResult[$value['league_id']] == 1 && in_array($value['tid'], $join)) {
                $win[] = $value['tid'];
            }
        }

        //发送邮件
        if (!empty($mailList)) {
            if (false === D('GMail')->sendAll($mailList)) {
                return false;
            }
        }

        //增加参与者次数
        if (!empty($join)) {
            $in = sql_in_condition($join);
            $sql = "update `g_count` set `league_fight`=`league_fight`+1 where `tid`{$in};";
            create_sql($sql, $this->mDBPath, $this->mFile);
        }

        //增加胜利者次数
        if (!empty($win)) {
            $in = sql_in_condition($win);
            $sql = "update `g_count` set `league_fight_win`=`league_fight_win`+1 where `tid`{$in};";
            create_sql($sql, $this->mDBPath, $this->mFile);
        }

        //清空公会战信息
        $this->clearRedis();

        return true;

    }

    //清空公会战信息
    private function clearRedis()
    {
        $keys = D('Predis')->cli('fight')->keys('lf:*');
        if (!empty($keys)) {
            D('Predis')->cli('fight')->del($keys);
        }
        return;
    }

}