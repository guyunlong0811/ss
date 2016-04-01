<?php
$date = array(
    '20160324',
    '20160325',
    '20160326',
    '20160327',
);

$table = array(
    'l_abyss',
    'l_activity_complete',
    'l_arena',
    'l_arena_battle',
    'l_cheat',
    'l_dynamic',
    'l_emblem',
    'l_equip_strengthen',
    'l_equip_upgrade',
    'l_iap',
    'l_instance',
    'l_item',
    'l_league',
    'l_league_battle',
    'l_league_battle_result',
    'l_league_boss',
    'l_league_dismiss',
    'l_league_donate',
    'l_league_fight',
    'l_league_food',
    'l_league_team',
    'l_league_team_member',
    'l_login',
    'l_mail',
    'l_member',
    'l_order',
    'l_partner',
    'l_pray',
    'l_share',
    'l_shop',
    'l_star',
    'l_team',
    'l_vip',
    't_daily_activity_bonus',
    't_daily_count',
    't_daily_event',
    't_daily_instance',
    't_daily_league',
    't_daily_online_bonus',
    't_daily_quest',
    't_daily_shop',
    't_specify_event',
    't_weekly_event',
);

$serverList = get_server_list();
foreach($serverList as $sid => $value1) {

    foreach ($date as $value2) {

        $path = 'script/Runtime/Logs/gamedb_backup/' . $sid . '/' . $value2 . '/';

        foreach ($table as $value3) {

            //修改表名
            $execAlter = "mysql -h{$value1['log']['DB_HOST']} -u{$value1['log']['DB_USER']} -p{$value1['log']['DB_PWD']} {$value1['log_dbname']} -e\"rename table ";

            $exec = "mysql -h{$value1['log']['DB_HOST']} -u{$value1['log']['DB_USER']} -p{$value1['log']['DB_PWD']} {$value1['log_dbname']} < {$path}{$value3}.sql";

            //修改表名
            $execAlter .= "{$value3} to {$value3}_{$sid}_{$value2},";

        }

        //修改表名
        $execAlter = substr($execAlter, 0, -1) . '"';
        exec($execAlter);

    }

}

//获取服务器列表
function get_server_list()
{

    $params['gid'] = 1;

    //发送协议
    $uc = uc_link($params, 'User.getServerList');

    //登录用户中心
    if (empty($uc['list'])) {
        return false;
    }

    $serverList = array();
    foreach ($uc['list'] as $value) {
        if (isset($serverList[$value['sid']]['channel'][$value['channel_id']])) {
            $serverList[$value['sid']]['channel'][$value['channel_id']]['name'][] = $value['name'];
        } else {
            $channel = array();
            $channel['channel_id'] = $value['channel_id'];
            $channel['name'][] = $value['name'];
            $channel['type'] = $value['type'];
            $channel['activation'] = $value['activation'];
            $channel['code'] = $value['code'];
            $channel['callback'] = $value['callback'];
            $serverList[$value['sid']]['channel'][$value['channel_id']] = $channel;
        }
        $serverList[$value['sid']]['dbname'] = $value['dbname'];
        $serverList[$value['sid']]['master']['DB_DEPLOY_TYPE'] = 0;
        $serverList[$value['sid']]['master']['DB_RW_SEPARATE'] = false;
        $serverList[$value['sid']]['master']['DB_HOST'] = $value['db_m_host'];
        $serverList[$value['sid']]['master']['DB_USER'] = $value['db_m_user'];
        $serverList[$value['sid']]['master']['DB_PWD'] = $value['db_m_pwd'];
        $serverList[$value['sid']]['master']['DB_PORT'] = $value['db_m_port'];
        $serverList[$value['sid']]['all']['DB_DEPLOY_TYPE'] = 1;
        $serverList[$value['sid']]['all']['DB_RW_SEPARATE'] = true;
        $serverList[$value['sid']]['all']['DB_HOST'] = $value['db_m_host'] . ',' . $value['db_s_host'];
        $serverList[$value['sid']]['all']['DB_USER'] = $value['db_m_user'] . ',' . $value['db_s_user'];
        $serverList[$value['sid']]['all']['DB_PWD'] = $value['db_m_pwd'] . ',' . $value['db_s_pwd'];
        $serverList[$value['sid']]['all']['DB_PORT'] = $value['db_m_port'] . ',' . $value['db_s_port'];
        $serverList[$value['sid']]['redis']['host'] = $value['redis_host'];
        $serverList[$value['sid']]['redis']['port'] = $value['redis_port'];
        $serverList[$value['sid']]['redis']['game'] = $value['redis_game'];
        $serverList[$value['sid']]['redis']['social'] = $value['redis_social'];
        $serverList[$value['sid']]['redis']['fight'] = $value['redis_fight'];
        $serverList[$value['sid']]['script_server_id'] = $value['script_server_id'];
        $serverList[$value['sid']]['platform']['url'] = $value['platform_url'];
        $serverList[$value['sid']]['platform']['sid'] = $value['platform_sid'];
        $serverList[$value['sid']]['log']['DB_HOST'] = $value['log_db_host'];
        $serverList[$value['sid']]['log']['DB_USER'] = $value['log_db_user'];
        $serverList[$value['sid']]['log']['DB_PWD'] = $value['log_db_pwd'];
        $serverList[$value['sid']]['log']['DB_PORT'] = $value['log_db_port'];
        $serverList[$value['sid']]['log_dbname'] = $value['log_dbname'];
    }

    return $serverList;
}


//连接用户中心
function uc_link($params, $method)
{

    //创建数据
    if (!isset($params['timestamp'])) {
        $params['timestamp'] = time();
    }
    $post = array();
    $post['method'] = $method;
    $post['params'] = $params;
    $post['sign'] = uc_sign_create($params);
    $post = json_encode($post);

    //发送协议
    $json = curl_link(UC_URL . '?c=Router&a=request', 'post', $post);

    //解码
    $arr = json_decode($json, true);
    if (isset($arr['result'])) {
        $ret = $arr['result'];
    } else {
        return false;
    }

    //检查sign
    $mySign = uc_sign_create($ret, 'respond');
    if ($mySign != $arr['sign']) {
        return false;
    }

    return $ret;
}

//生成sign
function uc_sign_create($params)
{
    //获取SALT
    $salt = 'forever!23';
    //排序
    ksort($params);
    //创建加密字符串
    $strSign = '';
    foreach ($params as $value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $strSign .= $value . '&';
    }
    $strSign .= $salt;
    $strSign = strtolower(md5($strSign));
    return $strSign;
}

//curl链接
function curl_link($host, $method = 'get', $data = '', $cookie = '', $return = true, $agent = 'WEBSERVER')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);

    if (strtolower($method) == 'post')
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    if (!empty($cookie))
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    $retData = curl_exec($ch);
    curl_close($ch);
    return $retData;
}
