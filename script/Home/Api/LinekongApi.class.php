<?php
namespace Home\Api;

use Think\Controller;

class LineKongApi extends BaseApi
{

    private $mDBPath;

    public function _initialize()
    {
        $this->mDBPath = COMMON_PATH . 'Common/db/';
    }

    public function logout()
    {

        //获取当前在线uid集合
        $keyList = D('Predis')->cli('game')->keys('u:*');
        $login = array();
        foreach ($keyList as $value) {
            $arr = explode(':', $value);
            $login[] = (int)$arr[1];
        }
//        dump($login);
        //获取上一次在线情况
        $json = D('Predis')->cli('game')->get('online_list');
        $lastLogin = json_decode($json, true);
//        dump($lastLogin);

        //计算没有下线的用户
        $stillLogin = array_intersect($login, $lastLogin);

        //计算已经下线的用户
        $logout = array_diff($lastLogin, $stillLogin);

        //如果有人下线
        if (!empty($logout)) {

            //拼登出玩家查询条件语句
            $where['uid'] = array('in', $logout);

            //查询玩家channel_uid
            $select = D('UCAccount')->field('`uid`,`channel_uid`')->where($where)->select();
            $channelUidList = array();
            foreach ($select as $value) {
                $channelUidList[$value['uid']] = $value['channel_uid'];
            }

            //查询玩家游戏信息
            $select = D('GTeam')->field('`tid`,`role_id`,`uid`,`level`,`exp`,`diamond_pay`,`diamond_free`,`channel_id`')->where($where)->select();
            $teamList = array();
            foreach ($select as $value) {
                $teamList[$value['uid']] = $value;
            }

            //生成发送list
            $curlList = array();
            $queue['command_id'] = 10003303;
            foreach ($logout as $value) {
                //channel_id
                $queue['channel_id'] = $teamList[$value]['channel_id'];
                //发送下线请求
                $body = array(
                    'user_id' => $channelUidList[$value],
                    'role_id' => $teamList[$value]['role_id'],
                    'logout_flag' => 1,
                    'role_occupation' => 0,
                    'role_level' => $teamList[$value]['level'],
                    'rating_id' => C('G_SID'),
                    'money1' => 0,
                    'money2' => 0,
                    'experience' => $teamList[$value]['exp'],
                );
                $queue['body'] = json_encode($body);
                $curlList[] = $queue;

            }

            //遍历数据
            $rs = array();
            if (!empty($curlList)) {
                $rs = D('ERating')->multi(C('G_SID'), $curlList);
            }

            if (!empty($rs)) {

                foreach ($rs['respond'] as $key => $value) {
                    $uid = $logout[$key];
                    $diamondFree = false;
                    $diamondPay = false;
                    if (isset($value['balance_info_list']['balance_info'])) {
                        if (isset($value['balance_info_list']['balance_info'][0])) {
                            foreach ($value['balance_info_list']['balance_info'] as $val) {
                                if ($val['subject_id'] == '4') {
                                    $diamondFree = $val['amount'];
                                } else if ($value['subject_id'] == '5') {
                                    $diamondPay = $val['amount'];
                                }
                            }
                        } else {
                            switch ($value['balance_info_list']['balance_info']['subject_id']) {
                                case '4':
                                    $diamondFree = $value['balance_info_list']['balance_info']['amount'];
                                    break;
                                case '5':
                                    $diamondPay = $value['balance_info_list']['balance_info']['amount'];
                                    break;
                            }
                        }
                    }

                    //如果与蓝港服务器有冲突，记录冲突日志并同步信息
                    if ($diamondFree && $diamondFree != $teamList[$uid]['diamond_free']) {
//                D('GTeam')->where("`tid`='{$teamList[$value]['tid']}'")->setField('diamond_free', $diamondFree);
                        $log = array(
                            'fg_uid' => $uid,
                            'team_id' => $teamList[$uid]['tid'],
                            'user_id' => $channelUidList[$uid],
                            'role_id' => $teamList[$uid]['role_id'],
                            'before' => $teamList[$uid]['diamond_free'],
                            'after' => $diamondFree,
                            'ctime' => time(),
                        );
                        write_log(json_encode($log) . "\r\n", 'eRating_error/');
                    }

                    if ($diamondPay && $diamondPay != $teamList[$uid]['diamond_pay']) {
//                D('GTeam')->where("`tid`='{$teamList[$value]['tid']}'")->setField('diamond_pay', $diamondPay);
                        $log = array(
                            'fg_uid' => $value,
                            'team_id' => $teamList[$uid]['tid'],
                            'user_id' => $channelUidList[$uid],
                            'role_id' => $teamList[$uid]['role_id'],
                            'before' => $teamList[$uid]['diamond_pay'],
                            'after' => $diamondPay,
                            'ctime' => time(),
                        );
                        write_log(json_encode($log) . "\r\n", 'eRating_error/');
                    }

                }

            }

        }

        //存储最新玩家ID
        D('Predis')->cli('game')->set('online_list', json_encode($login));
        return true;

    }

    //发送队列
    public function queue()
    {

        //时间
        $now = time();

        //查询全部数据
        $list = M('LinekongCommand')->order('`id` ASC')->select();

        //遍历数据
        if (!empty($list)) {

            //并发eRating
            $rs = D('ERating')->multi(C('G_SID'), $list);

            //删除当前已处理得请求
            $lastArr = end($list);//获取ID
            $maxId = $lastArr['id'];
            $where['id'] = array('elt', $maxId);
            D('LinekongCommand')->DeleteList($where);

            //将失败的消息塞回队列
            if (isset($rs['error'])) {
                $allResend = array();
                $allError = array();
                foreach ($rs['error'] as $value) {

                    if ($value['count'] >= 4) {
                        //如果重发3次都不成功则放入错误日志表
                        unset($value['count']);
                        $value['ctime'] = $now;
                        $allError[] = $value;
                    } else {
                        //放入表内重发
                        $value['ctime'] = $now;
                        $value['count'] += 1;
                        $allResend[] = $value;
                    }
                }

                //重发协议
                if(!empty($allResend)) {
                    D('LinekongCommand')->CreateAllData($allResend);
                }

                //错误日志
                if(!empty($allError)){
                    D('LinekongCommandError')->CreateAllData($allError);
                }
            }

        }

        return true;
    }

    //发送在线人数
    public function online()
    {
        //计算在线人数
        $keyList = D('Predis')->cli('game')->keys('u:*');
        $count = count($keyList);

        //获取channel
        $serverList = get_server_list();
        $channelList = $serverList[C('G_SID')]['channel'];
        $channelId = 0;
        foreach ($channelList as $key => $value) {
            if ($channelId == 0) {
                $channelId = $key;
            } else if ($channelId > $key) {
                $channelId = $key;
            }
        }

        //发送eRating
        $body = array();
        $body['server_id'] = C('G_SID');
        $body['data_info_list'] = array(
            'data_info' => array(
                'data_value' => $count,
                'data_type' => 1,
            ),
        );
        if (false === D('ERating')->index(10002003, C('G_SID'), $channelId, $body)) {
            return false;
        }

        //更新UC在线统计数据
        $serverList = get_server_list();
        D('OnlineStat')->record($serverList[C('G_SID')]['eRating']['gateway_id'], $count);
        return true;
    }

    //备份今天记录
    public function backup()
    {

        $serverList = get_server_list();
        $info = $serverList[C('G_SID')];

        $fromHost = $info['master']['DB_HOST'];
        $fromUser = $info['master']['DB_USER'];
        $fromPwd = $info['master']['DB_PWD'];
        $fromName = $info['dbname'];

        $toHost = $info['log']['DB_HOST'];
        $toUser = $info['log']['DB_USER'];
        $toPwd = $info['log']['DB_PWD'];
        $toName = $info['log_dbname'];

        //遍历日志表
        $date = date('Ymd', strtotime('yesterday'));
        $utime = strtotime(date('Y-m-d', strtotime('yesterday')) . ' ' . C('DAILY_UTIME'));

        //修改表名
        $execAlter = "mysql -h{$toHost} -u{$toUser} -p{$toPwd} {$toName} -e\"rename table ";

        //遍历表
        foreach ($this->mLogTableList as $value) {
            switch ($value) {
                case 'l_dynamic':
                case 'l_instance':
                case 'l_order':
                    $field = 'endtime';
                    break;
                case 'l_mail':
                    $field = 'create_time';
                    break;
                default:
                    $field = 'ctime';
            }
            $where = "{$field}>={$utime}";

            //将需要迁徙的数据库dump成sql
            $exec = "mysqldump -h{$fromHost} -u{$fromUser} -p{$fromPwd} {$fromName} {$value} --where=\"{$where}\" > {$this->mDBPath}{$value}.sql";
            exec($exec);

            //将sql执行到目标服务器
            $exec = "mysql -h{$toHost} -u{$toUser} -p{$toPwd} {$toName} < {$this->mDBPath}{$value}.sql";
            exec($exec);

            //修改表名
            $execAlter .= "{$value} to {$value}_{$date},";

            //删除sql
            unlink($this->mDBPath . $value . '.sql');
        }

        //每月一号备份 g_daily_register
        if(date('j') == '1'){
            //将需要迁徙的数据库dump成sql
            $exec = "mysqldump -h{$fromHost} -u{$fromUser} -p{$fromPwd} {$fromName} g_daily_register > {$this->mDBPath}g_daily_register.sql";
            exec($exec);

            //将sql执行到目标服务器
            $exec = "mysql -h{$toHost} -u{$toUser} -p{$toPwd} {$toName} < {$this->mDBPath}g_daily_register.sql";
            exec($exec);

            //修改表名
            $execAlter .= "g_daily_register to g_daily_register_{$date},";

            //删除sql
            unlink($this->mDBPath . 'g_daily_register.sql');
        }

        //修改表名
        $execAlter = substr($execAlter, 0, -1) . '"';
        exec($execAlter);

        //修改表名
        $execAlter = "mysql -h{$toHost} -u{$toUser} -p{$toPwd} {$toName} -e\"rename table ";

        //遍历表
        foreach ($this->mTruncateTableList as $value) {
            //将需要迁徙的数据库dump成sql
            $exec = "mysqldump -h{$fromHost} -u{$fromUser} -p{$fromPwd} {$fromName} {$value} > {$this->mDBPath}{$value}.sql";
            exec($exec);

            //将sql执行到目标服务器
            $exec = "mysql -h{$toHost} -u{$toUser} -p{$toPwd} {$toName} < {$this->mDBPath}{$value}.sql";
            exec($exec);

            //修改表名
            $execAlter .= "{$value} to {$value}_{$date},";

            //删除sql
            unlink($this->mDBPath . $value . '.sql');
        }

        //修改表名
        $execAlter = substr($execAlter, 0, -1) . '"';
        exec($execAlter);

        return true;

    }

}