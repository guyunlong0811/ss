<?php
namespace Home\Api;

use Think\Controller;

class LeagueArenaApi extends BaseApi
{

    const GROUP = 22;
    const REG_GROUP = 21;

    //公会匹配
    public function match()
    {
        //活动是否开启
        if (!$this->isEventEnable(self::GROUP)) {
            return true;
        }

        //查看今天是否已经匹配完成
        if (D('Predis')->cli('fight')->get('laf:match') == '1') {
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

        //获取报名公会信息
        $leagueArenaRList = D('GLeagueArena')->getAreaList();
//        dump($leagueArenaRList);

        //获取报名战队信息
        $select = D('GLeagueArenaTeam')->getList();
        if (empty($select)) {
            return true;
        }

        $leagueArenaTeam = array();
        $tidList = array();
        $leagueArenaTeamList = array();
        foreach ($select as $value) {
            $leagueArenaTeam[$value['id']] = $value;
            $arr['id'] = $value['id'];
            $arr['partner'] = json_decode($value['partner'], true);
            $leagueArenaTeamList[$value['league_id']][$value['tid']][] = $arr;
            if (!in_array($value['tid'], $tidList)) {
                $tidList[] = $value['tid'];
            }
        }
//        dump($leagueArenaTeamList);

        //获取玩家伙伴信息
        $field = array('tid', 'group', 'force');
        $where['tid'] = array('in', $tidList);
        $select = D('GPartner')->field($field)->where($where)->select();
        $partnerList = array();
        foreach ($select as $value) {
            $partnerList[$value['tid']][$value['group']] = $value['force'];
        }

        //遍历区域
        foreach ($leagueArenaRList as $kArea => $vAreaLeagueList) {
            $leagueAreaList = array();

            //遍历公会
            $i = 1;
            foreach ($vAreaLeagueList as $vAreaLeagueInfo) {

                $leagueAreaList[$vAreaLeagueInfo['league_id']]['league_id'] = (int)$vAreaLeagueInfo['league_id'];
                $leagueAreaList[$vAreaLeagueInfo['league_id']]['sequence'] = (int)$i;
                $leagueAreaList[$vAreaLeagueInfo['league_id']]['point'] = (int)$vAreaLeagueInfo['point'];

                //所有队伍战力
                $leagueBattleForce = array();

                //遍历公会每一个报名玩家
                foreach ($leagueArenaTeamList[$vAreaLeagueInfo['league_id']] as $kTid => $vBattleList) {

                    //遍历每一个报名小队
                    foreach ($vBattleList as $vBattleInfo) {

                        //计算小队总战力
                        $teamForceTotal = 0;
                        foreach ($vBattleInfo['partner'] as $vGroupId) {
                            $teamForceTotal += $partnerList[$kTid][$vGroupId];
                        }

                        //记录战力
                        $leagueBattleForce[$vBattleInfo['id']] = $teamForceTotal;

                    }

                }

                //计算每支公会排名前30的队伍战斗力
                arsort($leagueBattleForce);
                $leagueAreaList[$vAreaLeagueInfo['league_id']]['force'] = array_sum(array_slice($leagueBattleForce, 0, 30));
                $leagueAreaList[$vAreaLeagueInfo['league_id']]['battle'] = array_slice(array_keys($leagueBattleForce), 0, 30);
                ++$i;

            }

            //公会匹配
//            dump(array_values($leagueAreaList));
            $weekday = date('N');
            $leagueMatchResult = lua('league_battle_pvp', 'league_battle_pvp_matching', array(array_values($leagueAreaList), (int)$weekday));
//            dump($leagueMatchResult);

            $firstLeagueId = 0;
            $lastLeagueId = 0;

            //遍历公会
            foreach ($leagueMatchResult as $key => $leagueId) {

                //记录公会ID
                if ($firstLeagueId == 0) {
                    $firstLeagueId = $leagueId;
                } else {

                    //战役ID匹配
                    $leagueArenaTeam = $this->matchBattle($leagueArenaTeam, $leagueAreaList[$firstLeagueId]['battle'], $leagueAreaList[$leagueId]['battle']);
                    $leagueArenaTeam = $this->matchBattle($leagueArenaTeam, $leagueAreaList[$leagueId]['battle'], $leagueAreaList[$firstLeagueId]['battle']);

                    //还原
                    $firstLeagueId = 0;

                    //配置
                    $lastLeagueId = $leagueId;
                }

            }

            //轮空状态匹配
            if ($firstLeagueId > 0) {
                $leagueArenaTeam = $this->matchBattle($leagueArenaTeam, $leagueAreaList[$firstLeagueId]['battle'], $leagueAreaList[$lastLeagueId]['battle']);
            }

        }
//        dump(array_values($leagueArenaTeam));

        //清表
        D('GLeagueArenaTeam')->TruncateTable('g_league_arena_team');

        //重写数据
        D('GLeagueArenaTeam')->CreateAllData(array_values($leagueArenaTeam));

        //匹配完成标识
        D('Predis')->cli('fight')->set('laf:match', 1);
        return true;

    }

    //小队匹配
    private function matchBattle($leagueArenaTeam, $battleIdList, $targetBattleIdList)
    {

        //活动是否开启
        if (!$this->isEventEnable(self::GROUP)) {
            return true;
        }

        //将对手乱序
        shuffle($targetBattleIdList);

        //遍历我方小队
        foreach ($battleIdList as $battleId) {
            if (!empty($targetBattleIdList)) {
                //如果有对手则取出一个
                $leagueArenaTeam[$battleId]['opponent'] = array_shift($targetBattleIdList);
            } else {
                //没有对手则直接判胜
                $leagueArenaTeam[$battleId]['status'] = 1;
                D('Predis')->cli('fight')->hincrby('laf:fight', $leagueArenaTeam[$battleId]['league_id'], 1);
                D('Predis')->cli('fight')->hincrby('laf:win', $leagueArenaTeam[$battleId]['league_id'], 1);
            }
        }

        //返回数据
        return $leagueArenaTeam;
    }

    //公会战结算奖励
    public function account()
    {
        //活动是否开启
        if (!$this->isEventEnable(self::GROUP)) {
            return true;
        }

        //查看昨天有没有活动结束
        if (false === $this->isOverToday(self::GROUP)) {
            return true;
        }

        $now = time();

        //获取战斗信息
        $fightCountList = D('Predis')->cli('fight')->hgetall('laf:fight');
        $winCountList = D('Predis')->cli('fight')->hgetall('laf:win');

        //获取积分
        $winPoint = D('Static')->access('params', 'LEAGUE_PVP_WIN_GOLD');
        $losePoint = D('Static')->access('params', 'LEAGUE_PVP_LOSE_GOLD');

        //统一插入表
        $insertGLeagueArena = "insert into `g_league_arena` (`league_id`,`area`,`reg_tid`,`count`,`point`,`ctime`,`utime`) values ";
        $sqlGLeagueArena = '';

        $insertGLeagueArenaRank = "insert into `g_league_arena_rank` (`league_id`,`league_name`,`point`,`area`) values ";
        $sqlGLeagueArenaRank = '';

        //获取所有参战公会
        $leagueArenaList = D('GLeagueArena')->select();
        foreach ($leagueArenaList as $key => $value) {
            $point = $value['point'];
            $fightCount = $fightCountList[$value['league_id']] ? $fightCountList[$value['league_id']] : 0;
            $winCount = $winCountList[$value['league_id']] ? $winCountList[$value['league_id']] : 0;
            $loseCount = $fightCount - $winPoint;
            $loseCount = $loseCount >= 0 ? $loseCount : 0;
            $point += $winCount * $winPoint;
            $point += $loseCount * $losePoint;

            $newPoint = $leagueArenaList[$key]['point'] + $point;
            $sqlGLeagueArena .= "('{$value['league_id']}','{$value['area']}','{$value['reg_tid']}','{$value['count']}','{$newPoint}','{$value['ctime']}','{$now}'),";
        }
//        dump($leagueArenaList);

        //清表
        $sql = "truncate table `g_league_arena`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        //重写数据
        if (false !== $sql = insert_implode($insertGLeagueArena, $sqlGLeagueArena)) {
            create_sql($sql, $this->mDBPath, $this->mFile);
        }

        //获取区域信息
        $leagueArenaConfig = D('Static')->access('league_pvp');

        //生成新的排行榜
        foreach ($leagueArenaConfig as $value) {
            $select = D('GLeagueArena')->field('`g_league_arena`.`league_id`,`g_league`.`name` as `league_name`,`g_league_arena`.`point`,`g_league_arena`.`area`')->join('`g_league` ON `g_league`.`id`=`g_league_arena`.`league_id`')->where("`g_league_arena`.`area`='{$value['index']}'")->order('`g_league_arena`.`point` DESC,`g_league_arena`.`utime` ASC')->limit(3)->select();
            if (!empty($select)) {
                foreach ($select as $val) {
                    $sqlGLeagueArenaRank .= "('{$val['league_id']}','{$val['league_name']}','{$val['point']}','{$val['area']}'),";
                }
            }
        }
//        dump($rankList);

        //清表
        $sql = "truncate table `g_league_arena_rank`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        //重写数据
        if (false !== $sql = insert_implode($insertGLeagueArenaRank, $sqlGLeagueArenaRank)) {
            create_sql($sql, $this->mDBPath, $this->mFile);
        }

        //清空公会战信息
        $this->clearRedis();

        //重刷战队表
        $sql = "update `g_league_arena_team` set `opponent`=0 && `status`=2;";
        create_sql($sql, $this->mDBPath, $this->mFile);
        return true;

    }

    //周五结算第一名
    public function accountTop()
    {
        //活动是否开启
        if (!$this->isEventEnable(self::GROUP)) {
            return true;
        }

        //判断今天是否需要结算
        if (false === $this->isOpenToday(self::REG_GROUP)) {
            return true;
        }

        //获取区域信息
        $leagueArenaConfig = D('Static')->access('league_pvp');

        //查询是否需要结算
        $weekday = date('N');
        $eventConfig = D('Static')->access('event', self::REG_GROUP);
        foreach ($eventConfig as $value) {

            if ($value['start_date'] == $weekday) {

                $list = array();
                foreach ($leagueArenaConfig as $val) {
                    //查询第一
                    $field = array('area', 'league_id');
                    $info = D('GLeagueArena')->field($field)->where("`area`='{$val['index']}' && `point` > 0")->order('`point` DESC, `utime` ASC')->find();
                    if (!empty($info)) {
                        $list[$info['area']] = $info['league_id'];

                        //一次性公会资金奖励
                        $fund = D('Static')->access('league_pvp', $info['area'], 'pvp_bonus');
                        D('GLeague')->incAttr($info['league_id'], 'fund', $fund);

                    }
                }

                //修改结果信息
                $where['index'] = 'LEAGUE_ARENA_TOP';
                $data['value'] = json_encode($list);
                if (false === M('GParams')->where($where)->save($data)) {
                    return false;
                }
                D('Predis')->cli('game')->del('g_params');

                //清表
                $sql = "truncate table `g_league_arena`;";
                create_sql($sql, $this->mDBPath, $this->mFile);
                $sql = "truncate table `g_league_arena_team`;";
                create_sql($sql, $this->mDBPath, $this->mFile);
                return true;

            }

        }

        return true;

    }

    //榜首奖励
    public function bonus()
    {
        //活动是否开启
        if (!$this->isEventEnable(self::GROUP)) {
            return true;
        }

        C('G_BEHAVE', 'league_arena_top');

        //配置邮件ID
        $mailId = 70000;

        //获取榜首信息
        $info = D('GParams')->getValue('LEAGUE_ARENA_TOP');
        $info = json_decode($info, true);

        //遍历公会榜首
        if (empty($info)) {
            return true;
        }

        //生成邮件
        $mailList = array();
        foreach ($info as $area => $leagueId) {

            //获取公会所有战队ID
            $where['league_id'] = $leagueId;
            $tidList = D('GLeagueTeam')->where($where)->getField('tid', true);
            foreach ($tidList as $tid) {
                $mail = array();
                $mail['mail_id'] = $mailId + $area;
                $mail['tid'] = 0;
                $mail['target_tid'] = $tid;
                $mail['params'] = array();
                $mailList[] = $mail;
            }

        }

        //发送邮件
        if (!empty($mailList)) {
            if (false === D('GMail')->sendAll($mailList)) {
                return false;
            }
        }

        return true;

    }

    //清空公会战信息
    private function clearRedis()
    {
        $keys = D('Predis')->cli('fight')->keys('laf:*');
        if (!empty($keys)) {
            D('Predis')->cli('fight')->del($keys);
        }
        return;
    }

}