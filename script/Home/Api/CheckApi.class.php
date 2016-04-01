<?php
namespace Home\Api;

use Think\Controller;

class CheckApi extends BaseApi
{

    //发放奖励
    public function daily()
    {
        if(date('G') != 4){
            return true;
        }
        $ctime = D('TDailyCount')->order("`ctime` asc")->getField('ctime');
        if(empty($ctime)){
            return true;
        }
        $utime = get_daily_utime();
        if($utime > $ctime){

            $errorLog = 'SERVER:' . C('G_SID');
            if (C('WARNING_TYPE') == 'File') {
                write_log($errorLog, 'error/update/');
            } else if (C('WARNING_TYPE') == 'Mail') {
                @think_send_mail('error_report@forevergame.com', 'error_report', 'SS_UPDATE_ERROR(' . APP_STATUS . ')', $errorLog);
            }

        }
        return true;
    }

}