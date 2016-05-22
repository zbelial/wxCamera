<?php 
include('PathFile.php');
include_once('be.class.php');

$be = \classes\be::getInstance();
// echo $be->getAccessToken();

$action = $_GET['action'];

if ($action == 'getuserinfo') {

	echo $_GET['code'];
}

