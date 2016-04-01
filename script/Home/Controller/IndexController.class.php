<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{

    public function _initialize()
    {
        header_info();
    }

    public function index()
    {
        echo "手机游戏—绝对领域—脚本服务器";
    }

    public function showPhpInfo()
    {
        phpinfo();
    }

    //获取服务器时间
    public function getTimeStamp()
    {
        echo time();
    }

    //获取服务器时间
    public function timeDelta($time)
    {
        echo abs($time - time());
    }

    //清楚所有apc缓存
    public function clearApc()
    {
        //查看是否有token
        if (!isset($_POST['token']) || empty($_POST['token'])) {
            echo 'fail';
            return false;
        }
        //判断token是否正确
        $token = md5($_POST['key'] . C('CACHE_VERIFY'));
        if ($token != $_POST['token']) {
            echo 'fail';
            return false;
        }
        //清除apc
        if (isset($_POST['key']) && !empty($_POST['key'])) {
            S(C('APC_PREFIX') . $_POST['key'], null);
        } else {
            apc_clear_cache('user');
            apc_clear_cache();
        }
        //返回结果
        echo 'success';
        return true;
    }

    //设置IDENT
    public function setIdent()
    {
        //查看是否有token
        if (!isset($_POST['token']) || empty($_POST['token'])) {
            echo 'fail';
            return false;
        }
        //判断token是否正确
        $token = md5($_POST['ident'] . C('CACHE_VERIFY'));
        if ($token != $_POST['token']) {
            echo 'fail';
            return false;
        }
        //修改文件
        $str = "<?php\n";
        $str .= "return array(\n";
        $str .= "\t'IDENT' => {$_POST['ident']},\n";
        $str .= ");";
        $fp = fopen("./script/Home/Conf/ident.php", "w");
        $rs = fwrite($fp, $str);
        fclose($fp);
        //返回结果
        if ($rs > 0) {
            echo 'success';
        } else {
            echo 'fail';
        }
        return true;
    }

    //守护进程
    public function nohup()
    {
        $type = $_GET['type'];
//        echo exec("php {$type}.php Home");
        echo curl_link(SCRIPT_URL . "?method=Exec.index&interval={$type}");
        return true;
    }

}