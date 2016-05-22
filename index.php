<?php 
include('ddconfig/wxconfig.php');

$callbackURL = urlencode("https://www.baidu.com");

header("Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=".AppID."&redirect_uri=$callbackURL&response_type=code&scope=snsapi_userinfo#wechat_redirect");





