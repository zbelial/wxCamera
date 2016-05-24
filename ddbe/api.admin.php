<?php 
include('PathFile.php');
include_once('admin.class.php');
include(CONF_PATH.'/wxconfig.php');

$admin = \ddadmin\admin::getInstance();
// echo $be->getAccessToken();

$action = $_GET['action'];

echo json_encode(array('resp' => $admin->$action()));

