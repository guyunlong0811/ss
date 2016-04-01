<?php
namespace Home\Api;

use Think\Controller;

class ExecApi extends BaseApi
{

    public function index()
    {
        //参数检查
        if(empty($_GET['interval'])){
            return true;
        }

        //遍历服务器
        $serverList = get_server_list();
        $link = array();
        $serverIdList = array();
        foreach ($serverList as $sid => $value) {
            if (C('IDENT') == $value['script_server_id']) {
                $serverIdList[] = $sid;
                $link[] = SCRIPT_URL . '?method=Exec' . $_GET['interval'] . '.index&sid=' . $sid;
            }
        }

        //发送请求
        $rs = $this->curl_multi_link($link);
        //解析结果
        foreach ($rs as $key => $value) {
            echo $value;
            write_log($value, 'result/' . $_GET['interval'] . '/');
        }

        //返回
        return true;
    }

    //并发curl链接
    private function curl_multi_link($link)
    {

        //发起并发链接
        $mh = curl_multi_init();

        //循环发送
        $count = count($link);
        $ch = array();
        for ($i = 0; $i < $count; ++$i) {
            $ch[$i] = curl_init($link[$i]);
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch[$i], CURLOPT_TIMEOUT, 300);
            curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 300);
            curl_multi_add_handle($mh, $ch[$i]);
        }

        //发送连接
        $active = true;
        $mrc = CURLM_OK;
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) == -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        //获取返回
        $rs = array();
        for ($i = 0; $i < $count; ++$i) {
            $rs[$i] = curl_multi_getcontent($ch[$i]);
            curl_multi_remove_handle($mh, $ch[$i]);
            curl_close($ch[$i]);
        }

        //关闭并发连接
        curl_multi_close($mh);
        return $rs;
    }

}