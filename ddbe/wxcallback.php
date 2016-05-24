<?php

include('PathFile.php');
include(CONF_PATH.'/wxconfig.php');

$wechatObj = new wechatCallbackapiTest();
$wechatObj->valid();
class wechatCallbackapiTest
{
    // 验证配置必须有valid()方法
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        //extract post data
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
			<ToUserName><![CDATA[%s]></ToUserName>
			<FromUserName><![CDATA[%s]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[%s]></MsgType>
			<Content><![CDATA[%s]></Content>
			<FuncFlag>0</FuncFlag>
			</xml>";

            // 获取地理位置
            if ($postObj->Event == "LOCATION") {
                session_start();
                $_SESSION['x'] = 'dsadas';
                $_SESSION['y'] = $postObj->Longitude;
                setcookie('x',$postObj->Latitude);
                setcookie('y',$postObj->Longitude);

            }

            if(!empty( $keyword ))
            {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
                echo "Input something...";
            }
        }else {
            echo "";
            exit;
        }
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
// 获取普通access_token
    public function getAccessToken($appid,$appsecret){
        $accessTokenURL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $accessTokenResult = file_get_contents($accessTokenURL);
        $tokenResultData = json_decode($accessTokenResult);
        $accessToken = $tokenResultData->{'access_token'};
        $expiresIn = $tokenResultData->{'expires_in'};
        //echo "<hr/>access_token:".$accessToken."<hr/>";
        session_start();
        $_SESSION['accesstoken'] = $accessToken; // 缓存$accessToken
        return $accessToken;
    }
// 创建随机的十六位字符
    public function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
//echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
//$nowURL = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
//echo $nowURL;
// 创建jsapi的ticket
    public function createTicket($accessToken){
        $tempTicketURL = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$accessToken&type=jsapi";
        $tempTicketResult = file_get_contents($tempTicketURL);
        //echo "<hr/>ticket=".$tempTicketResult."<hr/>";
        $tempTicketData = json_decode($tempTicketResult);
        $tempTicket = $tempTicketData->{'ticket'};
        //echo "<br/><hr/>ticket为:".$tempTicket."<br/><hr/>";
        return $tempTicket;
    }
// 返回最终的字符串签名
    public function checkJsSDKSignature($tmpArr)
    {
        $tmpStr ='';
        foreach ($tmpArr as $key => $val) {
            $tmpStr.= $key.'='.$val.'&';
        }
        $tmpStr = substr($tmpStr, 0, strlen($tmpStr) - 1);
        $tmpStr = sha1($tmpStr);
        return $tmpStr;
    }
// 创建带有签名一整套config的数据
    public function createConfig($appid,$appsecret,$url){
        $timeStamp = time(); // 创建时间戳
//    $nowURL = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];  // 创建目前页面的URL
        $nowURL = $url;
        session_start();
        $accessToken = $this->getAccessToken($appid,$appsecret);	// 创建access_token
        $ticket = $this->createTicket($accessToken);	// 创建jsapi ticket
//    echo "<hr/>ticket:".$ticket."<hr/>";
        $nonceStr = $this->createNonceStr();	// 创建随机的十六位码
        $jsParams = array(
            'jsapi_ticket' => $ticket,
            'noncestr'  => $nonceStr,
            'timestamp' => $timeStamp,
            'url'       => $nowURL
        );
        $jsSignature = $this->checkJsSDKSignature($jsParams); // 生成签名串
        // 往html回调数据 congfig配置数据
        return array(
            'js_ticket' => $ticket,
            'noncestr'  => $nonceStr,
            'timestamp' => $timeStamp,
            'js_signature' => $jsSignature,
            'appid' => $appid,
            'app_secret' => $appsecret,
        );
    }
    // 绑定设备
    public function bindDevice($accesstoken,$openid,$ticket,$devceid){
        $url = "https://api.weixin.qq.com/device/bind?access_token=$accesstoken";
        alert($accesstoken);
        $dataJson = '{
                        "ticket": "'.$ticket.'",
                        "device_id": "'.$devceid.'",
                        "openid": "'.$openid.'"
                    }';
//        $postjson = json_encode($dataJson);  不许要json_encode！！否则绑定设备会传参错误！！！
        $returnData = $this->http_post_json($url,$dataJson);
        $dataInfo = json_decode($returnData);
        $respinfo = $dataInfo->{'base_resp'};
        $errmsg = $respinfo->{'errmsg'};
        return  $errmsg;  //返回ok或者fail
    }
    // 解绑设备
    /**unbindDevice($accesstoken,$openid,$ticket,$deviceid);**/
    public function unbindDevice($accesstoken,$openid,$ticket,$deviceid){
        $url = "https://api.weixin.qq.com/device/unbind?access_token=$accesstoken";
        $dataJson = '{
                        "ticket": "'.$ticket.'",
                        "device_id": "'.$deviceid.'",
                        "openid": "'.$openid.'"
                    }';
        $returnData = $this->http_post_json($url,$dataJson);
        $dataInfo = json_decode($returnData);
        $respinfo = $dataInfo->{'base_resp'};
        $errmsg = $respinfo->{'errmsg'};
        return  $errmsg;  //返回ok或者fail
    }
    // php post传递参数
    private function http_post_json($url, $jsonStr){
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
    } // end function
    /* get
        $ch = curl_init("http://www.domain.com/api/index.php?test=1") ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        echo $output = curl_exec($ch) ;
    */
    // 获取已经绑定了的设备
    public function  getBindWXDevice($accesstoken,$openid){
        $url = "https://api.weixin.qq.com/device/get_bind_device?access_token=$accesstoken&openid=$openid";
        $Result = file_get_contents($url);
        return $Result;
    }
}
//$configArr = $wechatObj->createConfig($appid,$appsecret); // 创建配置数组
//
///***
// *  echo  "<hr/>appId:".$configArr['appid'];
//echo  "<hr/>timestamp:". $configArr['timestamp'];
//echo  "<hr/>nonceStr:". $configArr['noncestr'];
//echo  "<hr/>signature:". $configArr['js_signature'];
// */
//
//
//echo $configArr['appid'].",".$configArr['timestamp'].",".$configArr['noncestr'].",".$configArr['js_signature'];
?>