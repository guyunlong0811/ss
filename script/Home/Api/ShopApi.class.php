<?php
namespace Home\Api;

use Think\Controller;

class ShopApi extends BaseApi
{

    //查看是否有商店开启
    public function open()
    {

        //标识
        $flag = true;

        //查看神秘商店是否开启
        if (!D('GShop')->isOpen('102')) {

            //当前时间
            $now = time();
            $date = time2format($now, 2);

            //获取神秘商店开启时间
            $shopConfig = D('Static')->access('shop', 102);
            $openTime = explode(';', $shopConfig['refresh_time']);

            //查看是否可以开启
            foreach ($openTime as $value) {
                $showTime = D('Static')->access('shop', '102', 'show_time');
                $starttime = strtotime($date . ' ' . $value);
                $endtime = $starttime + (60 * $showTime);
                if ($starttime <= $now && $now <= $endtime) {
                    //清除神秘商店数据
                    if (false === D('GShopMystery')->TruncateTable('g_shop_mystery')) {
                        $flag = false;
                    }
                    //开启神秘商店
                    if (false === D('GShop')->open('102', $endtime)) {
                        $flag = false;
                    }
                }
            }
        }

        //返回
        return $flag;

    }

    //清除过期商店
    public function clean()
    {
        $now = time();
        $where['dtime'] = array('elt', $now);
        $select = M('GShop')->where($where)->select();
        if (!empty($select)) {
            foreach ($select as $value) {
                $log = array();
                $log['tid'] = $value['tid'];
                $log['type'] = $value['type'];
                $log['starttime'] = $value['ctime'];
                $log['endtime'] = $value['dtime'];
                $log['ctime'] = $now;
                $logList[] = $log;
            }

            if (false === D('LShop')->CreateAllData($logList)) {
                return false;
            }
        }
        return M('GShop')->where($where)->delete();
    }

}