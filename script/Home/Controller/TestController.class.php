<?php
namespace Home\Controller;

use Think\Controller;

class TestController extends Controller
{

    private $host;//请求地址
    private $getData;//请求地址

    //测试数据
    private $TestData = array(

        'Notice' => array(

            'send' => array(
                'gid' => 1,
                'sid' => 1,
                'send_tid' => 0,
                'content' => 'test',
                'level' => 1,
                'endtime' => 0,
                'interval' => 0,
            ),

        ),

    );

    public function _initialize()
    {

        header_info();

        if (isset($_GET['controller']))
            $this->controller = $_GET['controller'];

        if (isset($_GET['action']))
            $this->action = $_GET['action'];

        $this->host = SCRIPT_URL;
        $this->getData['method'] = $this->controller . '.' . $this->action;

    }

    public function index()
    {

        //获取协议配置
        $protocol = get_config('protocol', array($this->controller, $this->action,));
        $url = $this->host . '?';
        foreach ($this->getData as $key => $value) {
            $url .= $key . '=' . $value . '&';
        }
        $url = substr($url, 0, -1);
        $url .= '&sid=' . $_GET['sid'];
        if ($protocol['verify']) {

            if (isset($this->TestData[$this->controller][$this->action])) {
                $params = $this->TestData[$this->controller][$this->action];
            } else {
                $params = array();
            }
            $params['timestamp'] = time();

            //生成sign
            $params['sign'] = uc_sign_create($params, 'request');

            //生成json
            $post = json_encode($params);

        }

        //发送协议
        $ret = curl_link($url, 'post', $post);
        echo '<div style="max-width:960px; word-break:break-all;">';
        echo $url;
        echo '<hr />';
        //打印结果
        echo($ret);
        echo "</div>";

    }

    //清楚所有apc缓存
    public function clearApc()
    {
        if (apc_clear_cache('user'))
            echo 'success';
        else
            echo 'fail';
    }

    //快速测试
    public function fast_test()
    {
        C('G_SID', 104);
        change_db_config(C('G_SID'), 'all');
        A('Mail', 'Api')->clean();
//        A('LeagueArena', 'Api')->match();
//        A('LeagueArena', 'Api')->account();
//        A('LeagueArena', 'Api')->accountTop();
//        A('LeagueArena', 'Api')->bonus();
        return;
    }

}