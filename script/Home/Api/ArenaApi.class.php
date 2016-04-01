<?php
namespace Home\Api;

use Think\Controller;

class ArenaApi extends BaseApi
{

    //竞技场排名奖励
    public function rank()
    {

        //获取当前小时
        $hour = date('G');
        if($hour == 9){
            $type = 3;
        }else if($hour == 21){
            $type = 1;
        }else{
            return true;
        }

        //获取排名奖励配置
        $rankConfig = D('Static')->access('rank_bonus', $type);

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

}