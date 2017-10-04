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





/* ==========================================================================
	0. Universal Code: Set Header And Start Session
	========================================================================== */
header('Content-Type: application/json');
session_start();

/* ==========================================================================
	1. Error Response Function: Response Error Code And Error Message
	========================================================================== */
function response($code = 0, $errMsg = 'success') {
	echo json_encode(['code' => $code, 'errMsg' => $errMsg]);
	exit(0);
}

/* ==========================================================================
	2. PDO-Based Database Connection
	========================================================================== */
try {
	$connect = new PDO("mysql:host=$addr;dbname=$dbname;charset=utf8", $username, $password);
}
catch(PDOException $err) {
	response(2333, '数据库连接出错，请联系管理员');
}

/* ==========================================================================
	3. Exist Check Function: Check Whether Required Paraments Exist
	========================================================================== */
function exist_check() {
	for($i = 0; $i < func_num_args(); $i++)
		if (!isset($_POST[func_get_arg($i)])) {
			header('Location: http://p1.img.cctvpic.com/20120409/images/1333902721891_1333902721891_r.jpg');
			exit();
		}
	}

/* ==========================================================================
	4. Session Check Function: Check Whether Vistor Is Logged In
	========================================================================== */
function session_check() {
	if (!isset($_SESSION['user'])) response(233, '请登录系统！');
}

/* ==========================================================================
	5. Database Query Function: Return Query Result
	   Response Error If Query Operation Fails
	========================================================================== */
function db_query($query_sql, $query_arr = [], $one_row_only = false) {
	global $connect;
	$query_stmt = $connect->prepare($query_sql);
	$query_stmt->execute($query_arr);
	$query_result = $one_row_only ?
		$query_stmt->fetch(PDO::FETCH_ASSOC) : $query_stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($query_result === false) response(100, '操作失败，请联系管理员');
	return $query_result;
}
