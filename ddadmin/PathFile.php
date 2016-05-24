<?php 

// ROOT
if(!define('ROOT_PATH')) 			define('ROOT_PATH', dirname(dirname(__FILE__)));

// config
if(!define('CONF_PATH')) 			define('CONF_PATH', ROOT_PATH.'/ddconfig');

// ddfe
if(!define('FE_PATH')) 				define('FE_PATH', 	ROOT_PATH.'/ddfe');

// ddbe
if(!define('BE_PATH')) 				define('BE_PATH', 	ROOT_PATH.'/ddbe');
