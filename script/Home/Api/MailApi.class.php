<?php
namespace Home\Api;

use Think\Controller;

class MailApi extends BaseApi
{

    const MAX_ROW = 10000;

    //清除过期邮件
    public function clean()
    {
        //sql
        $sqlBase = "insert into `l_mail` (`id`,`tid`,`type`,`title`,`from`,`des`,`item_1_type`,`item_1_value_1`,`item_1_value_2`,`item_2_type`,`item_2_value_1`,`item_2_value_2`,`item_3_type`,`item_3_value_1`,`item_3_value_2`,`item_4_type`,`item_4_value_1`,`item_4_value_2`,`open_script`,`behave`,`ctime`,`dtime`,`create_time`,`status`) values ";

        $now = time();
        $where['dtime'] = array('elt', $now);
        $count = M('GMail')->where($where)->count();
        $num = ceil($count / self::MAX_ROW);

        for ($i = 1; $i <= $num; ++$i) {
            $start = ($i - 1) * self::MAX_ROW;
            $select = M('GMail')->where($where)->order('`id` ASC')->limit($start, self::MAX_ROW)->select();
            $sql = '';
            foreach ($select as $value) {
                $sql .= "\r\n" . "(null,'{$value['tid']}','{$value['type']}','{$value['title']}','{$value['from']}','{$value['des']}','{$value['item_1_type']}','{$value['item_1_value_1']}','{$value['item_1_value_2']}','{$value['item_2_type']}','{$value['item_2_value_1']}','{$value['item_2_value_2']}','{$value['item_3_type']}','{$value['item_3_value_1']}','{$value['item_3_value_2']}','{$value['item_4_type']}','{$value['item_4_value_1']}','{$value['item_4_value_2']}','{$value['open_script']}','{$value['behave']}','{$value['ctime']}','{$value['dtime']}','{$now}','2'),";
            }
            if(false !== $sql = insert_implode($sqlBase, $sql)){
                create_sql($sql, $this->mDBPath, $this->mFile);
            }
        }

        //删除sql
        for ($i = 1; $i <= $num; ++$i) {
            $sqlDel = "delete from `g_mail` where `dtime` <= '{$now}' limit " . self::MAX_ROW . ";";
            create_sql($sqlDel, $this->mDBPath, $this->mFile);
        }

        return true;
    }

}