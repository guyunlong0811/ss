<?php
namespace Home\Api;

use Think\Controller;

class VipApi extends BaseApi
{

    //发放奖励
    public function bonus()
    {
        //获取VIP
        $field = array('`g_team`.`tid`', '`g_team`.`nickname`', '`g_vip`.`index`');
        $where['index'] = array('between', array('1001', '1999'));
        $select = D('GVip')->field($field)->join("`g_team` on `g_team`.`tid` = `g_vip`.`tid`")->where($where)->select();

        //创建邮件
        $mailList = array();
        foreach ($select as $value) {
            $mail = array();
            $mail['mail_id'] = $value['index'] - 1000 + 5000;
            $mail['tid'] = 0;
            $mail['target_tid'] = $value['tid'];
            $mail['params']['target_nickname'] = $value['nickname'];
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