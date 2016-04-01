<?php
namespace Home\Api;

use Think\Controller;

class EventApi extends BaseApi
{

    private $mEventStartTime;
    private $mEventEndTime;
    const LEAGUE_FIGHT_OPEN_TIME = 600;

    //查看活动的开启和关闭
    public function check()
    {

        //查询活动静态表
        $eventConfig = D('Static')->access('event');
        $eventEnable = D('GParams')->getValue('ENABLE_EVENT');
        $eventEnable = json_decode($eventEnable, true);

        //查询当前活动状态
        $eventList = D('GEvent')->getAll();

        //逐个查询
        foreach ($eventConfig as $key => $value) {;

            $status = 0;//redis用
            $index = 0;//redis用
            $ps = '';//redis用

            foreach ($value as $k => $val) {

                if (in_array($k, $eventEnable)) {//活动开启
                    //查询活动是否开启
                    $open = $this->isOpen($val);
                    if ($open == 1) {
                        $status = 1;
                        $index = $k;
                    }
                } else {//如果活动关闭
                    $open = 0;
                }

                //如果活动列表没有该活动
                if (empty($eventList[$val['index']])) {

                    $add['id'] = $val['index'];
                    $add['group'] = $val['group'];
                    $add['status'] = $open;
                    $add['ps'] = '';
                    if ($open == '1') {//如果开始活动则走开始活动逻辑
                        $ps = $add['ps'] = $this->open($val['group']);
                    }
                    D('GEvent')->CreateData($add);
                    $this->notice($val['prompt_type'], $val['prompt_string']);//发送开始公告
                } else {

                    //如果活动状态一致则不变
                    if ($open == $eventList[$val['index']]['status'] || $eventList[$val['index']]['status'] == '2') {
                        continue;
                    } else {
                        $update['id'] = $val['index'];
                        $update['status'] = $open;
                        if ($open == '1') {//如果开始活动则走开始活动逻辑
                            $this->notice($val['prompt_type'], $val['prompt_string']);//发送开始公告
                            $ps = $update['ps'] = $this->open($val['group']);
                        } else {
                            $this->close($val['group'], $val['index']);
                            $update['ps'] = '';
                        }
                        D('GEvent')->UpdateData($update);
                    }

                }

            }

            //修改redis活动状态
            $event['status'] = $status;
            if ($status == 1) {
                $event['index'] = $index;
                $event['ps'] = $ps;
            } else {
                $event['index'] = 0;
                $event['ps'] = '';
            }
            D('Predis')->cli('game')->hmset('event:' . $key, $event);

        }

        return true;
    }

    //关闭活动
    private function close($group, $index)
    {
        switch ($group) {
            case '7'://失灭之城
                $where['id'] = $index;
                $ps = M('GEvent')->where($where)->getField('ps');
                if (!isset($ps['league']) || $ps['league'] == '0') {
                    $add['league_id'] = 0;
                    $add['league_name'] = '';
                    $add['idol_tid'] = 0;
                    D('LLeagueBattleResult')->CreateData($add);
                }
                break;
            case '15'://公会战
                D('Predis')->cli('game')->set('league_dismiss_status', '1');
                break;
        }
    }

    //开启活动条件
    private function open($group)
    {
        $arrPs['starttime'] = $this->mEventStartTime;
        $arrPs['endtime'] = $this->mEventEndTime;
        switch ($group) {
            case '7'://失灭之城
                $arrPs['league'] = 0;
                //查询前50名平均等级
                $order = array('level' => 'desc',);
                $level = M('GTeam')->order($order)->limit(50)->avg('level');
                $level = round($level);
                $arrPs['map'] = lua('league_area_battle', 'league_area_battle', array($level));
                //清空redis
                $idol['league_id'] = 0;
                $idol['league_name'] = '';
                $idol['tid'] = 0;
                $idol['nickname'] = '';
                D('Predis')->cli('game')->del('idol');
                D('Predis')->cli('game')->hmset('idol', $idol);
                break;
        }
        $ps = json_encode($arrPs);
        return $ps;
    }

    //活动参加条件检查(时间&等级)
    private function isOpen($config)
    {

        $now = time();//当前时间戳

        //检查现在是否是活动开放时间
        switch ($config['type']) {

            case '0'://永久任务
                return 1;
                break;

            case '1'://每日任务
                $this->mEventStartTime = strtotime(time2format(null, 2) . ' ' . $config['start_time']);
                $this->mEventEndTime = strtotime(time2format(null, 2) . ' ' . $config['end_time']);
                if ($this->mEventStartTime <= $now && $now < $this->mEventEndTime) {
                    return 1;
                } else {
                    return 0;
                }
                break;

            case '2'://每周任务
                $w = date('N');//今天星期几
                $start = $w - $config['start_date'];
                $end = $config['end_date'] - $w;
                $this->mEventStartTime = strtotime(time2format(strtotime('-' . $start . ' days'), 2) . ' ' . $config['start_time']);
                $this->mEventEndTime = strtotime(time2format(strtotime('+' . $end . ' days'), 2) . ' ' . $config['end_time']);
                if ($this->mEventEndTime <= $this->mEventStartTime) {
                    $this->mEventEndTime += 7 * 86400;
                }

                if ($this->mEventStartTime <= $now && $now < $this->mEventEndTime) {
                    return 1;
                } else {
                    return 0;
                }
                break;

            case '3'://指定日活动
                $this->mEventStartTime = strtotime($config['start_date'] . ' ' . $config['start_time']);
                $this->mEventEndTime = strtotime($config['end_date'] . ' ' . $config['end_time']);
                if ($this->mEventStartTime <= $now && $now < $this->mEventEndTime) {
                    return 1;
                } else {
                    return 0;
                }
                break;
        }

    }

    //发送活动开始公告
    private function notice($type, $string)
    {
        if ($type == '0') {
            return true;
        } else if ($type == '1') {
            $notice = D('Static')->access('event_string', $string);
            if (!D('GChat')->sendNoticeMsg(0, $notice['des'], $notice['show_level'])) {
                return false;
            }
            return true;
        }
    }

}