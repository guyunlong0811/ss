<?php
namespace Home\Controller;

use Think\Controller;

class RouterController extends Controller
{

    private $mProtocol;//缓存对象
    private $mResult = false;//处理结果
    private $mRespond = '';//回应数据

    //发起请求
    public function request()
    {

        //获取协议
        $method = explode('.', $_GET['method']);
        $c = $method[0];
        $a = $method[1];
        $this->mProtocol = get_config('protocol', array($c, $a,));
        if (empty($this->mProtocol)) return;

        $post = file_get_contents("php://input");
        if (!empty($post)) {
            $_POST = json_decode(trim($post), true);
        }

        //写入数据库配置
        if (isset($_GET['sid']) && $_GET['sid'] > 0) {
            C('G_SID', $_GET['sid']);
        }
        change_db_config(C('G_SID'), 'all');
//        dump($_POST);

        //验证salt
        if ($this->mProtocol['verify']) {

            //参数&sign验证
            $verify = $this->verify();
//            dump($verify);
            if ($verify !== true) {
                C('G_ERROR', 'params_error');
                return false;
            }

        } else {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');
        }

        //执行协议逻辑
        G('begin');
        $this->mResult = A($c, 'Api')->$a();
        G('end');
        ini_set('memory_limit', '128M');

        //写日志
        $this->writeRequestLog();

        return;

    }

    //验证body
    private function verify()
    {

        //数据处理
        if (isset($this->mProtocol['params'])) {
            foreach ($this->mProtocol['params'] as $key => $value) {
                if (!isset($_POST[$key])) {
                    return $key;
                }
                $_POST[$key] = urldecode($_POST[$key]);

                switch ($value['type']) {
                    case 'number':
                        if (!is_numeric($_POST[$key])) {
                            return $key;
                        }
                        break;
                    case 'string':
                        if (!is_string($_POST[$key])) {
                            return $key;
                        }
                        if (isset($value['regex']) && !preg_match($value['regex'], $_POST[$key])) {
                            return $key;
                        }
                        $_POST[$key] = trim($_POST[$key]);
                        break;
                    case 'json':
                        $_POST[$key] = json_decode($_POST[$key], true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            return $key;
                        }
                        break;
                }

            }
        }

        //检查游戏ID
        if (isset($_POST['gid']) && $_POST['gid'] != get_config('game_id')) {
            return 'gid';
        }

        //检查时间戳
        if (isset($_POST['pts']) && abs(time() - $_POST['pts']) > get_config('uc_verify', 'time_limit')) {
            return 'pts';
        }

        //检查时间戳
        if (abs(time() - $_POST['timestamp']) > get_config('uc_verify', 'time_limit')) {
            return 'timestamp';
        }

        //生成sign
        $arr = $_POST;
        unset($arr['sign']);
        $mySign = uc_sign_create($arr, 'request');

        //比较sign
        if ($_POST['sign'] != $mySign) {
            return 'sign';
        }

        return true;

    }

    //写日志
    private function writeRequestLog()
    {

        $log = '';
        //get参数
        foreach ($_GET as $key => $value) {
            $log .= "{$key} : {$value}\n";
        }
        $now = time2format();
        $log .= "server : " . C('G_SID') . "\n";
        $log .= "time : {$now}\n";
        $log .= "========================";
        write_log($log, $this->mProtocol['log_path'], $this->mProtocol['type']);
    }

    //记录日志
    private function cLog()
    {
        $str = $_GET['method'] . '#' . time2format(null, 4) . '#' . time();
        $filename = date('Ymd');
        $file = "./log/{$filename}.log";
        $wfp = fopen($file, "a");
        fputs($wfp, $str . "\r\n");
        fclose($wfp);
    }

    //返回方法
    private function respond()
    {
        if (isset($this->mProtocol['respond']) && $this->mProtocol['respond'] === false) {
            return;
        }
        //返回参数
        if (!$this->mResult) {
            $this->mRespond['status'] = 'fail';
            $this->mRespond['error'] = C('G_ERROR_EXEC');
        } else {
            $this->mRespond['status'] = 'success';
        }
        $this->mRespond['sid'] = C('G_SID');
        $this->mRespond['run'] = G('begin', 'end', 6);
        $this->mRespond['timestamp'] = time();
        //输出结果
        header_info('json');
        echo json_encode($this->mRespond);
        return;
    }

    //空操作
    public function _empty()
    {
        return false;
    }

    //析构
    public function __destruct()
    {
        $this->respond();//返回方法
    }

}