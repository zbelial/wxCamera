<?php 
// 包含一些公用方法
namespace classes;

include('PathFile.php');
include('db.class.php');
require_once(CONF_PATH.'/wxconfig.php');

define('GET_ACCESSTOKEN_URL', 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&'); 
define('ACTOKEN', 'access_token');

class be extends \db
{
    protected static $_instance = null;
	
	function __construct() {
        $dbarr = include(CONF_PATH.'/dbconfig.php');
        parent::__construct($dbarr['DBNAME'], $dbarr['DBHOST'], $dbarr['DBUSER'], $dbarr['DBPASS']);
    }

    // 单例
    public function getInstance() {

        if (self::$_instance === null) {
            self::$_instance = new self();
        } 
        return self::$_instance;
    }


    // 获取普通的accesstoken
    public function getAccessToken() {
        if ($_COOKIE[ACTOKEN] || isset($_COOKIE[ACTOKEN])) {
            return $_COOKIE[ACTOKEN];
        } else {
            return $this->newAccesstoken();
        }
    }

    // 获取新的accesstoken;
    public function newAccesstoken() {
        // 开始获取accesstoken
        $RequestAccesstokenURL = GET_ACCESSTOKEN_URL.'appid='.AppID.'&secret='.AppSecret;
        $AccessTokenJson = $this->requestWithGet($RequestAccesstokenURL);
        $AccessTokenResult = json_decode($AccessTokenJson);

        $Accesstoken = $AccessTokenResult->{'access_token'};
        $expires_in = $AccessTokenResult->{'expires_in'}; // 有效时间

        // access_token存入Cookie
        setcookie(ACTOKEN, $Accesstoken,time()+$expires_in);

        return $Accesstoken;
    }

    // 获取openid
    private function getOpenid($url) {
        $result = $this->requestWithGet($url);
        $json = json_decode($result);

        $returnArr = array('openid' => $json->{'openid'}, 'access_token' => $json->{'access_token'});

        return $returnArr;
    }

    // 通过openid获取用户信息
    public function getUserInfo($url) {
        $parameterArr = $this->getOpenid($url);
        //https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
        $accesstoken = $parameterArr['access_token'];
        $openid = $parameterArr['openid'];

        $result = $this->requestWithGet("https://api.weixin.qq.com/sns/userinfo?access_token=$accesstoken&openid=$openid&lang=zh_CN");

        $resultjson = json_decode($result);
        $nickname = $resultjson->{'nickname'};
        $sex = $resultjson->{'sex'};
        $headimgurl = $resultjson->{'headimgurl'};

        /* var_dump($result);
        [json object]
        {  
           "openid":" OPENID",
           " nickname": NICKNAME,
           "sex":"1",
           "province":"PROVINCE"
           "city":"CITY",
           "country":"COUNTRY",
            "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46", 
            "privilege":[
            "PRIVILEGE1"
            "PRIVILEGE2"
            ],
            "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
        }
        */

        $ResArr = $this->select_Tab('wx_users')->select_Obj('*')->select_Where("wx_users_openid=$openid")->search_command();

        if (count($ResArr) > 0) {
            echo "已经存在";
        } else {
            $ResArr = $this->select_Tab('wx_users')->select_Obj('wx_users_openid,wx_users_sex,wx_users_img,wx_users_nickname')->set_newObj("'$openid','$sex','$headimgurl','$nickname'")->insert_command();

            echo $ResArr['pass'];
        }

        
    }

    // 删除cookie
    public function destoryCookie() {
        foreach($_COOKIE as $key=>$val){
            setcookie($key,"",time()-100);
        }
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










