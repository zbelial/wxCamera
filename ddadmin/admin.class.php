<?php 
// 包含一些公用方法
namespace ddadmin;

include('PathFile.php');
include_once(BE_PATH.'/be.class.php');

class admin extends \classes\be
{
    protected static $_instance = null;
	
	function __construct() {
        parent::__construct();
    }

    // 单例
    public function getInstance() {

        if (self::$_instance === null) {
            self::$_instance = new self();
        } 
        return self::$_instance;
    }

    // Denglu
    public function admin_login() {
        $wx_admin_token = $this->admin_protoken();
        $wx_admin_user = mysql_escape_string($_GET['wx_admin_user']);
        $wx_admin_pass = mysql_escape_string($_GET['wx_admin_pass']);

        $loginres = $this->select_Tab('wx_admin')->select_Obj('count(*)')->select_Where("wx_admin_user='$wx_admin_user' and wx_admin_pass = '$wx_admin_pass'")->search_command();

        var_dump($loginres);

        // if (count($loginres[0])) {
        //     # code...
        // }
    }

    private function admin_protoken() {
        return sha1(time().$_SERVER['HTTP_HOST'].mt_rand(0,9999));
    }

    // 发送post请求
    public function requestWithPost($url,$dataStr){

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
    //    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $arr = curl_exec($ch);
        curl_close($ch);
        return $arr;
    }

    // 发送get请求
    public function requestWithGet($url) {
      return file_get_contents($url);
    }

    public function http_post_json($url, $jsonStr){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    } 

}

$admin = admin::getInstance();

$action = $_GET['action'];
$admin->$action();

// echo "test";







