<?php 
include('PathFile.php');
include_once('be.class.php');

$be = \classes\be::getInstance();
// echo $be->getAccessToken();

$action = $_GET['action'];

if ($action == 'getuserinfo') {

	$code = $_GET['code'];
	$getOpenidURL = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".AppID."&secret=".AppSecret."&code=$code&grant_type=authorization_code";

	$be->getUserInfo($getOpenidURL);
}

