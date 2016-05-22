<?php 
include('PathFile.php');
include_once('be.class.php');
include(CONF_PATH.'/wxconfig.php');
include_once('wxcallback.php');

$be = \classes\be::getInstance();
// echo $be->getAccessToken();

$action = $_GET['action'];

// if ($action == 'getuserinfo') {

// 	$code = $_GET['code'];
// 	$getOpenidURL = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".AppID."&secret=".AppSecret."&code=$code&grant_type=authorization_code";

// 	$be->getUserInfo($getOpenidURL);
// }

function convert_data($data){
	$image=base64_decode(str_replace('data:image/jpeg;base64,','',$data));
	save_to_file($image);
}

function save_to_file($image){
	$filename='ddstorage/'.time();
	$filename.='.jpg';
	$fp = fopen($filename,'w') or die("Unable to open file!");
	$result = fwrite($fp,$image);
	$returnArr = array(
		'errorcode' => $result, 
		'imgurl' => PROJECT_URL.'ddbe/'.$filename
	);

	echo json_encode($returnArr);
	fclose($fp);
}

// convert_data($_REQUEST['data']);

switch ($action) {
	case 'getuserinfo':
		$code = $_GET['code'];
		$getOpenidURL = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".AppID."&secret=".AppSecret."&code=$code&grant_type=authorization_code";

		$be->getUserInfo($getOpenidURL);
		break;

	case 'takeuserinfo':
		session_start();
		$returnArr = array('nickname' => $_SESSION['nickname'], 'sex' => $_SESSION['sex'],'img' => $_SESSION['headimgurl']);
		echo json_encode($returnArr);
		break;
	
	case 'uploadCamera':
		convert_data($_REQUEST['data']);
		break;

	case 'config':

		$wechatObj = new wechatCallbackapiTest();
		$configArr = $wechatObj->createConfig(AppID,AppSecret,$_POST['url']); // 创建配置数组

		$returnArr = array(
	        "myappid"=> $configArr['appid'],
	        "mytemestamp"=> $configArr['timestamp'],
	        "mynoncestr"=> $configArr['noncestr'],
	        "mysignature"=> $configArr['js_signature']
	    );
	    
	    echo json_encode($returnArr);
		break;

	default:
		break;
}

