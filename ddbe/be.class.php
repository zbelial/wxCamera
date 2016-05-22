<?php 
// 包含一些公用方法
namespace classes;

include('PathFile.php');
require_once(ROOT_PATH.'/Config_wx.php');
require_once(UTILTIES_PATH.'/http.interface.php');
require_once(UTILTIES_PATH.'/api_sql.class.php');

use Utilties\interfaces\http as ahttp;
use api\sqlClass as api_sql_utilties;

define('GET_ACCESSTOKEN_URL', 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&'); 

/**
* 公共api
*/
class common_api extends api_sql_utilties\api_sql implements ahttp\http_method_interface
{
	
	function __construct($initDB = false, $DBname, $DBip, $DBuser, $DBpwd) {
        
        if ($initDB == true) {
            parent::__construct($DBname, $DBip, $DBuser, $DBpwd);
        } else {
            
        }
    }

	/*
     * implements http_method_interface
     */
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
    /*
     * end implements http_method_interface
     */

    public function getAccessToken() {
    	// $tokenJson = json_decode($this->requestWithGet(ACCESSTOKEN_URL));
    	// return $tokenJson->{'accessToken'};
        //STOR_PATH

        $tokenJson = file_get_contents(ACTOKEN_JSON_PATH);
        $tokendata = json_decode($tokenJson);

        $now_get_time = time();
        $destory_time = $tokendata->{'destory_time'};
        $expires_in = $tokendata->{'expires_in'};

        // 没有过期
        if ((($destory_time - $now_get_time) < $expires_in) && (($destory_time - $now_get_time) > 0)) {
        // if (($destory_time - $now_get_time) < 1) { date('Y-m-d H:i:s', 1156219870);
            // echo "没有过期".($destory_time - $now_get_time).','.date('Y-m-d H:i:s', $destory_time).','.date('Y-m-d H:i:s', $now_get_time).$tokendata->{'access_token'};
            return $tokendata->{'access_token'};
            // return $this->newAccesstoken();
        } else {
            // echo "过期了";
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

        $nowtime = time();
        $destory_time = $nowtime + $expires_in; // 过期时间

        $respArr = array('access_token' => $Accesstoken, 'destory_time' => $destory_time, 'expires_in' => $expires_in);

        $this->write_to_actoken_json($respArr);

        return $Accesstoken;
    }

}










