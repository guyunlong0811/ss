<?php
namespace Home\Api;

use Think\Controller;

class LogApi extends BaseApi
{

    //清除7天前记录
    public function clean()
    {

        $index = 'id';

        //计算时间
        $ts[1] = strtotime(time2format(strtotime('-1 days'), 2) . ' ' . C('DAILY_UTIME'));
        $ts[3] = strtotime(time2format(strtotime('-3 days'), 2) . ' ' . C('DAILY_UTIME'));
        $ts[7] = strtotime(time2format(strtotime('-7 days'), 2) . ' ' . C('DAILY_UTIME'));

        //遍历日志表
        foreach ($this->mLogTableList as $value) {

            //数据库where字段
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

            //删除天数
            switch ($value) {
                case 'l_activity_complete':
                case 'l_league_team':
                case 'l_mail':
                case 'l_pray':
                case 'l_order':
                case 'l_team':
                case 'l_vip':
                    $day = 7;
                    break;
                case 'l_item':
                case 'l_arena_battle':
                    $day = 3;
                    break;
                default:
                    $day = 3;
            }

            //查询出最大的主键ID
            $where[$field] = array('lt', $ts[$day]);
            $maxId = M()->table($value)->where($where)->max($index);

            //按主键删除数据(防止死锁)
            if (!empty($maxId)) {
                $sql = "delete from `{$value}` where `{$index}`<={$maxId};";
                create_sql($sql, $this->mDBPath, $this->mFile);
            }
        }

        return true;

    }

    //备份今天记录
    public function backup()
    {

        //遍历日志表
        $sid = C('G_SID');
        $date = date('Ymd', strtotime('yesterday'));
        $utime = strtotime(date('Y-m-d', strtotime('yesterday')) . ' ' . C('DAILY_UTIME'));

        //目录创建
        $dbPath = LOG_PATH . 'gamedb_backup/' . $sid . '/' . $date . '/';
        if (!is_dir($dbPath)) {
            if (!mkdir($dbPath, 0777, true)) {
                return false;
            }
        }

        $serverList = get_server_list();
        $info = $serverList[$sid];

        $fromHost = $info['master']['DB_HOST'];
        $fromUser = $info['master']['DB_USER'];
        $fromPwd = $info['master']['DB_PWD'];
        $fromName = $info['dbname'];

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
            $exec = "mysqldump -h{$fromHost} -u{$fromUser} -p{$fromPwd} {$fromName} {$value} --where=\"{$where}\" > {$dbPath}{$value}.sql";
            exec($exec);
        }

        //每月一号备份 g_daily_register
        if (date('j') == '1') {
            //将需要迁徙的数据库dump成sql
            $exec = "mysqldump -h{$fromHost} -u{$fromUser} -p{$fromPwd} {$fromName} g_daily_register > {$dbPath}g_daily_register.sql";
            exec($exec);
        }

        //遍历表
        foreach ($this->mTruncateTableList as $value) {
            //将需要迁徙的数据库dump成sql
            $exec = "mysqldump -h{$fromHost} -u{$fromUser} -p{$fromPwd} {$fromName} {$value} > {$dbPath}{$value}.sql";
            exec($exec);
        }

        return true;

    }

}