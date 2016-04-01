<?php
namespace Home\Api;

use Think\Controller;

class FriendApi extends BaseApi
{
    //删除体力赠送信息
    public function clean()
    {
        $keys = D('Predis')->cli('social')->keys('fv:*');
        if (!empty($keys)) {
            D('Predis')->cli('social')->del($keys);
        }
        return true;
    }
}