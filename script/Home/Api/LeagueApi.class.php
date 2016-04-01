<?php
namespace Home\Api;

use Think\Controller;

class LeagueApi extends BaseApi
{

    //活跃度同步
    public function activitySync()
    {
        //查看今天是否已经匹配完成
        $keys = D('Predis')->cli('game')->keys('lg:*');
        if (!empty($keys)) {
            foreach ($keys as $key) {

                if (!!$activity = D('Predis')->cli('game')->hget($key, 'activity')) {
                    $arr = explode(':', $key);
                    $where['id'] = $arr[1];
                    $data['activity'] = $activity;
                    D('GLeague')->UpdateData($data, $where);
                }

            }
        }
        return true;
    }

}