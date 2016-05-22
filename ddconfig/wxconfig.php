<?php 
header('content-type:text/html;charset=utf-8');
date_default_timezone_set("Asia/Shanghai");

/*************************************
 *
 *			 微信公众号配置
 *
 *************************************/
if(!define("TOKEN")) 				define("TOKEN","ddtc");
if(!define("AppID")) 				define('AppID','wx8f3ec8fe252ae718');
if(!define("AppSecret")) 			define('AppSecret','96076e05f0daad0d10232e9688360803');
