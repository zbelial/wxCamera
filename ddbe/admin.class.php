<?php 
// 包含一些公用方法
namespace ddadmin;
include('PathFile.php');
include('db.class.php');
include(CONF_PATH.'/wxconfig.php');

class admin extends \db
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

    public function admin_getlist() {
        if (!$this->admin_vertifytoken()) {
            return false;
        }

        $state = $_POST['state'];

        $questions = $this->select_Tab('wx_questions')->select_Obj('*')->select_Where("wx_questions_state = '$state'")->select_other("ORDER BY wx_questions_id DESC")->search_command();

        return $questions;
    }

    public function admin_getdesc() {
        $wx_questions_id = $_POST['wx_questions_id'];

        $questions = $this->select_Tab('wx_questions')->select_Obj('*')->select_Where("wx_questions_id='$wx_questions_id'")->search_command(); // [0]

        $imgs = $this->select_Tab('wx_img')->select_Obj('*')->select_Where("wx_questions_id='$wx_questions_id'")->search_command();

        //wx_reply
        $replys = $this->select_Tab('wx_reply')->select_Obj('*')->select_Where("wx_questions_id='$wx_questions_id'")->search_command();


        return array(
            'wx_questions' => $questions[0],
            'wx_img' => $imgs,
            'wx_reply' => $replys,
            );

    }

    // 回复
    public function admin_reply() {
        $wx_questions_id = $_POST['wx_questions_id'];
        $wx_reply_content = $_POST['wx_reply_content'];

        // 使用事务提交
        $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
        $this->PDO_LINK->beginTransaction();

        $data = array('wx_questions_state' => '1');
        $update = $this->select_Tab('wx_questions')->select_Where("wx_questions_id='$wx_questions_id'")->update_new_command($data);

        $wx_reply_id = time();

        $insertArr = array();
        array_push($insertArr,array($wx_reply_id,$wx_reply_content,$wx_questions_id));

        $ResReply = $this->select_Tab('wx_reply')->select_Obj('wx_reply_id,wx_reply_content,wx_questions_id')->insert_new_command($insertArr);

        if ($update['pass'] == false || $ResReply['pass'] == false) {
            // 如果某一步错误的话就回滚操作
            $this->PDO_LINK->rollBack(); 
            $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
            return false;
        }

        $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
        return true;
    }

    // Denglu
    public function admin_login() {
        $wx_admin_token = $this->admin_protoken();
        $wx_admin_user = mysql_escape_string($_POST['wx_admin_user']);
        $wx_admin_pass = mysql_escape_string($_POST['wx_admin_pass']);

        $loginres = $this->select_Tab('wx_admin')->select_Obj('count(*)')->select_Where("wx_admin_user='$wx_admin_user' and wx_admin_pass = '$wx_admin_pass'")->search_command();

        // var_dump($loginres);

        if ($loginres[0]['count(*)']) {
            return false;
        }

        // 使用事务提交
        $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
        $this->PDO_LINK->beginTransaction(); 

        $data = array('wx_admin_token' => $wx_admin_token);
        $update = $this->select_Tab('wx_admin')->select_Where("wx_admin_user='$wx_admin_user'")->update_new_command($data);

        if ($update['pass'] == false) {
            // 如果某一步错误的话就回滚操作
            $this->PDO_LINK->rollBack(); 
            $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
            return false;
        }

        $this->PDO_LINK->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
        setcookie('token',$wx_admin_token);
        return true;
    }

    // 验证token
    public function admin_vertifytoken() {
        if (!$_COOKIE['token']) {
            return false;
        }

        $token = $_COOKIE['token'];

        $tokenres = $this->select_Tab('wx_admin')->select_Obj('count(*)')->select_Where("wx_admin_token='$token'")->search_command();

        if ($tokenres[0]['count(*)'] > 0) {
            return true;
        }
        return false;
    }

    // 生成token
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







