<?php
namespace Home\Api;

use Think\Controller;

class DailyRegisterApi extends BaseApi
{
    //每月1号清除每日登录活动表
    public function clean()
    {
        if (date('j') == '1') {
            $sql = "truncate table `g_daily_register`;";
            return create_sql($sql, $this->mDBPath, $this->mFile);
        }
        return true;
    }
}