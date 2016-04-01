<?php
namespace Home\Api;

use Think\Controller;

class TruncateApi extends BaseApi
{

    //清除每日表
    public function daily()
    {

        $sql = "truncate table `t_daily_count`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        $sql = "truncate table `t_daily_event`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        $sql = "truncate table `t_daily_instance`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        $sql = "truncate table `t_daily_quest`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        $sql = "truncate table `t_daily_shop`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        $sql = "truncate table `t_daily_online_bonus`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        $sql = "truncate table `t_daily_activity_bonus`;";
        create_sql($sql, $this->mDBPath, $this->mFile);

        return true;
    }

    //清除每周表
    public function weekly()
    {
        $weekday = date('w');
        if ($weekday == 1) {
            $sql = "truncate table `t_weekly_event`;";
            return create_sql($sql, $this->mDBPath, $this->mFile);
        }
        return true;
    }

}