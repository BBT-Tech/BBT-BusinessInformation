<?php
/* --- Configuration Part Start --- */

//Database configurations:
$addr = 'localhost';                            //Database address
$dbname = 'business_information';               //Database name
$username = 'DB_username_for_this_project';     //Username for project database
$password = 'corresponding_password';           //Password for project database

//Super administrator's username and password SHA256 hash value
$super_username = 'test';
$super_password = '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08';

/* --- Configuration Part End --- */





//Universal header set code
header('Content-Type: application/json');

//Database connection based on PDO:
try {
	$connect = new PDO("mysql:host=$addr;dbname=$dbname;charset=utf8", $username, $password);
} catch(PDOException $ex) {
    response(2333, '数据库连接出错，请联系管理员');
}

//Return code process function:
function response($code, $errMsg = 'success') {
	echo json_encode(['code' => $code, 'errMsg' => $errMsg]);
	exit(0);
}

//Check whether required paraments exist or not
function existCheck() {
	for($i = 0; $i < func_num_args(); $i++)
		if (!isset($_POST[func_get_arg($i)])) {
			header('Location: http://p1.img.cctvpic.com/20120409/images/1333902721891_1333902721891_r.jpg');
			exit(0);
		}
}
