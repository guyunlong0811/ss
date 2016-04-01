<?php
namespace Home\Api;

use Think\Controller;

class NoticeApi extends BaseApi
{

    //发送跑马灯公告
    public function send()
    {
        return D('GChat')->sendNoticeMsg($_POST['send_tid'], $_POST['content'], $_POST['level'], $_POST['endtime'], $_POST['interval']);
    }

    //发送跑马灯公告
    public function cancel()
    {
        return D('GChat')->cancelNoticeMsg($_POST['send_tid'], $_POST['content'], $_POST['level'], $_POST['endtime'], $_POST['interval']);
    }

}