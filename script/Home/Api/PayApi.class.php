<?php
namespace Home\Api;

use Think\Controller;

class PayApi extends BaseApi
{

    const MAX = 10000;//最多一次处理条目数

    //清除过期订单
    public function clean()
    {
        //获取条件
        $now = time();
        $endtime = $now - 86400;
        $where['ctime'] = array('lt', $endtime);

        //获取需要处理的条目
        $count = M('GOrder')->where($where)->count();

        //一次处理1W条
        $num = ceil($count / self::MAX);

        //循环处理
        $sqlBase = "insert into `l_order` (`id`,`tid`,`cash_id`,`price`,`channel_id`,`order_id`,`platform_order_id`,`verify`,`starttime`,`endtime`,`status`) values ";
        $now = time();
        for($i=1;$i<=$num;++$i){
            //查询数据
            $start = ($i - 1) * self::MAX;
            $select = M('GOrder')->where($where)->limit($start, self::MAX)->select();
            $sql = '';

            //组合sql
            foreach ($select as $value) {
                $sql .= "\r\n" . "(null,'{$value['tid']}','{$value['cash_id']}','{$value['price']}','{$value['channel_id']}','{$value['order_id']}','','','{$value['ctime']}','{$now}','-2'),";
            }

            if(false !== $sql = insert_implode($sqlBase, $sql)){
                create_sql($sql, $this->mDBPath, $this->mFile);
            }

        }

        //删除数据
        $sql = "delete from `g_order` where `ctime`<{$endtime};";
        create_sql($sql, $this->mDBPath, $this->mFile);
        return true;
    }

}