<?php 
// 包含一些公用方法
namespace classes;

include('PathFile.php');
include('db.class.php');
include(CONF_PATH.'/wxconfig.php');

define('GET_ACCESSTOKEN_URL', 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&'); 
define('ACTOKEN', 'access_token');
define('ACTOKEN_JSON','accesstoken.json');
define('ACTOKEN_JSON_PATH', CONF_PATH.'/'.ACTOKEN_JSON);

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

    // 写入记录accesstoken
    private function write_to_actoken_json($dataArray) {
        $path = ACTOKEN_JSON_PATH;
        $json = json_encode($dataArray);

        $myfile = fopen($path, "w+") or die("Unable to open file!");

        // $content = "新建:";
        // // 输出单行直到 end-of-file
        // while(!feof($myfile)) {
        //   $content += fgets($myfile);
        // }

        $fwrite = fwrite($myfile, $json);
        fclose($myfile);
    }

    // 客户查看自己提交的工单
    public function clientGetList($state) {
        session_start();
        $openid = $_SESSION['openid'];
        $questions = $this->select_Tab('wx_questions')->select_Obj('*')->select_Where("wx_questions_openid='$openid' and wx_questions_state = '$state'")->select_other("ORDER BY wx_questions_id DESC")->search_command();

        return $questions;
    }

    // 客户查看详情
    public function clientGetDesc($wx_questions_id) {

        $questions = $this->select_Tab('wx_questions')->select_Obj('*')->select_Where("wx_questions_id='$wx_questions_id'")->search_command(); // [0]

        $imgs = $this->select_Tab('wx_img')->select_Obj('*')->select_Where("wx_questions_id='$wx_questions_id'")->search_command();


        $replys = $this->select_Tab('wx_reply')->select_Obj('*')->select_Where("wx_questions_id='$wx_questions_id'")->search_command();

        return array(
            'wx_questions' => $questions[0],
            'wx_img' => $imgs,
            'wx_reply' => $replys,
            );

    }

    // 上传工单问题
    public function commmitQuestion($openid,$wx_questions_content,$wx_questions_place,$wx_questions_point,$wx_questions_id,$imgUrlArray) {
        // 使用事务提交
        $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
        $this->PDO_LINK->beginTransaction(); 

        // 插入问题记录
        $ResQuestion = $this->select_Tab('wx_questions')->select_Obj('wx_questions_id,wx_questions_openid,wx_questions_content,wx_questions_place,wx_questions_point')->set_newObj("'$wx_questions_id','$openid','$wx_questions_content','$wx_questions_place','$wx_questions_point'")->insert_command();

        $wx_img_array = array();
        foreach ($imgUrlArray as $key => $value) {
            array_push($wx_img_array,array($wx_questions_id , $value));
        }

        // 插入图片记录
        $ResImg = $this->select_Tab('wx_img')->select_Obj('wx_questions_id,wx_img_url')->insert_new_command($wx_img_array);

        if ($ResQuestion['pass'] == false || $ResImg['pass'] == false ) {
            // 如果某一步错误的话就回滚操作
            $this->PDO_LINK->rollBack(); 
            $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
            return false;
        }

        $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
        return true;

    }

    // 下载图片到本地
    public function downloadImg($serverId) {
        $access_token = $this->getAccessToken();
        //http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=MEDIA_ID

        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=serverId";

        $isActokenInvalid = file_get_contents($url);
        $isActokenInvalidjson = json_decode($isActokenInvalid);

        if($isActokenInvalidjson->{'errcode'} != '0') {
            $access_token = $this->newAccesstoken();
        }

        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$serverId"; // ok

        $path = 'ddstorage/';
        session_start();
        $time = time();
        
        if ($time == $_SESSION['time']) {
            $time+=1;
        }

        $_SESSION['time'] = $time;

        $filename = $time.mt_rand(0,9).'.jpg';

        $res = $this->GrabImage($url,$filename,$path);

        return array(
            'errorcode' => $res, 
            'filename' => $path.time().'.jpg',
            'msg' => $isActokenInvalidjson->{'errcode'} 
        );
    }

    private function GrabImage($url,$filename='',$path) {
        if($url==''):return false;endif;
        if($filename=='') {
            $ext=strrchr($url,'.');
        if($ext!='.gif' && $ext!='.jpg'):return false;endif;$filename=date('dMYHis').$ext;
        }
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        $fp2=@fopen($path.$filename, 'a');
        $result = fwrite($fp2,$img);
        fclose($fp2);

        return $result;
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

        $ResArr = $this->select_Tab('wx_users')->select_Obj('*')->select_Where("wx_users_openid='$openid'")->search_command();

        session_start();
        $_SESSION['openid'] = $openid;
        $_SESSION['nickname'] = $nickname;
        $_SESSION['sex'] = $sex;
        $_SESSION['headimgurl'] = $headimgurl;

        if (count($ResArr) > 0) {
            // var_dump($ResArr);
            // echo "已经存在";

            header("Location: ../ddfe/index.html");
            exit();
        } else {
            // 如果此用户不存在的话就 加入此用户
            $InsertResArr = $this->select_Tab('wx_users')->select_Obj('wx_users_openid,wx_users_sex,wx_users_img,wx_users_nickname')->set_newObj("'$openid','$sex','$headimgurl','$nickname'")->insert_command();

            if($InsertResArr['pass'] == true) {

                header("Location: ../ddfe/index.html");
                exit();
            } else {

                session_destroy();
                echo "网络问题导致错误，请您重新进入";
                exit();
            }
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

    public function test() {
        echo "test";
    }

}










