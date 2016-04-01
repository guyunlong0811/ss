<?php
namespace Home\Api;

use Think\Controller;

class AbyssBattleApi extends BaseApi
{

    const ERROR_LIMIT = 10;

    //BOSS复活公告
    public function reborn()
    {
        $abyssConfig = D('Static')->access('abyss_battle');
        foreach ($abyssConfig as $key => $value) {
            $open = D('Predis')->cli('fight')->hget('ab:m:' . $value['index'], 'open');//开放时间
            if (empty($open)) {
//                $this->notice($value['index']);
            } else {
                $now = time();
                if ($now >= $open && $open >= ($now - 60 - self::ERROR_LIMIT)) {
                    $this->notice($value['index']);
                }
            }
        }
        return true;
    }

    //发送活动开始公告
    private function notice($abyss)
    {
        //发送击杀公告
        $params['monstername'] = D('Static')->access('abyss_battle', $abyss, 'name');
        $noticeConfig = D('SEventString')->getConfig('ABYSS_BATTLE_REFRESH', $params);
        if (!D('GChat')->sendNoticeMsg(0, $noticeConfig['des'], $noticeConfig['show_level'])) {
            return false;
        }
        return true;
    }

}