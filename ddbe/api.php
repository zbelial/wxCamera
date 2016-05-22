<?php 
include('PathFile.php');
include_once('be.class.php');

$be = \classes\be::getInstance();
// echo $be->getAccessToken();

$action = $_GET['action'];

// if ($action == 'getuserinfo') {

// 	$code = $_GET['code'];
// 	$getOpenidURL = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".AppID."&secret=".AppSecret."&code=$code&grant_type=authorization_code";

// 	$be->getUserInfo($getOpenidURL);
// }

switch ($action) {
	case 'getuserinfo':
		$code = $_GET['code'];
		$getOpenidURL = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".AppID."&secret=".AppSecret."&code=$code&grant_type=authorization_code";

		$be->getUserInfo($getOpenidURL);
		break;

	case 'takeuserinfo':
		session_start();
		$returnArr = array('nickname' => 1, 'sex' => 1,'img' => 1);
		echo json_encode($returnArr);
		break;
	
	default:
		break;
}

