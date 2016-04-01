<?php
namespace Home\Api;

use Think\Controller;

class PrayApi extends BaseApi
{

    //限时祈愿结算
    public function timed()
    {

        //查询限时祈愿数据
        $PrayTimedConfig = D('StaticDyn')->access('pray');

        //解析数据
        $mailList = array();
        foreach($PrayTimedConfig as $value){

            //活动是否开启
            if($value['status'] == '0'){
                continue;
            }

            //是否是当前服务器
            if($value['server'] != '0'){
                //查询是不是当前服务器
                $arrServer = explode('#', $value['server']);
                if(!in_array(C('G_SID'), $arrServer)){
                    continue;
                }
            }

            //活动结束时间是否在24小时内
            $now = strtotime(time2format(null, 2) . ' ' . C('DAILY_UTIME'));
            $yesterday = $now - 86400;
            if($yesterday > $value['endtime'] || $value['endtime'] >= $now){
                continue;
            }

            //获取排名奖励配置
            $rankConfig = D('Static')->access('rank_bonus', $value['rank_type']);

            //计算需要的最大排名
            $end = end($rankConfig);
            $max = $end['rank_end'];

            //排名
            $rankList = D('GPrayTimed')->rank($value['index'], $max);

            //创建邮件
            $i = 1;
            foreach ($rankList as $key => $data) {
                foreach ($rankConfig as $config) {
                    if ($config['rank_start'] <= $i && $i <= $config['rank_end']) {
                        $mail = array();
                        $mail['mail_id'] = $config['rank_mail'];
                        $mail['tid'] = 0;
                        $mail['target_tid'] = $data['tid'];
                        $mail['params']['target_nickname'] = $data['nickname'];
                        $mail['params']['rank'] = $i;
                        $mail['params']['point'] = $data['point'];
                        $mailList[] = $mail;
                        unset($rankList[$key]);
                    } else {
                        break;
                    }
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

}