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

	case 'downloadimg':
		$serverId = $_POST['serverId'];
		echo json_encode($be->downloadImg($serverId));
		break;

	case 'commit':
		session_start();
		$openid = $_SESSION['openid'];
		$wx_questions_content = $_POST['wx_questions_content'];
		$wx_questions_place = $_POST['wx_questions_place'];
		$wx_questions_point = $_POST['wx_questions_point'];

		$wx_img_url_data = $_POST['wx_img_url_data'];
		$wx_img_url_arr = json_decode($wx_img_url_data);
		$wx_questions_id = time();

		// echo $wx_img_url_arr[0];$be->downloadImg($serverId)
		$imgUrlArray = array(); // 图片名字URL


		// 把图片上传并且把url存储进来
		foreach ($wx_img_url_arr as $key => $value) {
			// value -> serverId -> mediaId
			$res = $be->downloadImg($value);

			if ($res['errorcode'] == true) {
				$imgurl = PROJECT_URL."ddbe/".$res['filename'];
				array_push($imgUrlArray,$imgurl);
			} else {
				echo json_encode(array('errorcode' => false));

				exit();
			}
		}

		$commitResult = $be->commmitQuestion($openid,$wx_questions_content,$wx_questions_place,$wx_questions_point,$wx_questions_id,$imgUrlArray);

		echo json_encode(array('errorcode' => $commitResult));

		break;

	case 'unsolvelist':
		session_start();
		if (!$_SESSION['openid']) {
			exit();
		}
		$questions = $be->clientGetList('0');
		echo json_encode(array('resp' => $questions));
		break;

	case 'solvedlist':
		session_start();	
		if (!$_SESSION['openid']) {
			exit();
		}
		$questions = $be->clientGetList('1');
		echo json_encode(array('resp' => $questions));
		break;

	case 'quesdesc':
		session_start();
		if (!$_SESSION['openid']) {
			exit();
		}
		$wx_questions_id = $_POST['wx_questions_id'];
		$result = $be->clientGetDesc($wx_questions_id);

		echo json_encode(array('resp' => $result));
		break;

	default:
		break;
}

