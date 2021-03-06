<?php
require_once './config.php';

$_POST['operation'] = isset($_POST['operation']) ? $_POST['operation'] : 'all';
switch ($_POST['operation']) {
	/* ==========================================================================
	   Module 0. Get All Accounts' Information
	   ========================================================================== */
	case 'all':
		session_check();
		$result = db_query('
			SELECT
				username, name, is_minister, register_time, update_time
			FROM accounts
		');
		if (empty($result)) response(1, '数据库中暂无任何账号信息');

		echo json_encode([
			'code' => 0,
			'accounts' => array_values($result)
		]);
		break;

	/* ==========================================================================
	   Module 1. Create A New Account
	   ========================================================================== */
	case 'create':
		session_check();
		exist_check('username', 'name', 'password');
		if (!$_SESSION['is_minister']) response(2, '权限验证出错！');

		$result = db_query(
			'SELECT * FROM accounts WHERE username = ?',
			array($_POST['username'])
		);
		if (!empty($result) || ($_POST['username'] == $super_username))
			response(3, '该用户名已存在，请更换');

		$salt = sha1((mt_rand()));
		$sql = '
			INSERT INTO accounts
				(username, name, salt, salted_password_hash, is_minister)
			VALUES (?,?,?,?,?)
		';
		db_query($sql, [
			$_POST['username'],
			$_POST['name'],
			$salt,
			hash('sha256', $_POST['password'] . $salt),
			($_SESSION['user'] == $super_username ? 1 : 0)
		]);
		response();
		break;

	/* ==========================================================================
	   Module 2. Modify An Account's Name And Password
	   ========================================================================== */
	case 'modify':
		session_check();
		if (!$_SESSION['is_minister']) response(4, '权限验证出错！');
		exist_check('username', 'new_name', 'set_new_password');
		$modify_minister = ($_SESSION['user'] == $super_username) ? 1 : 0;

		$sql = '
			SELECT * FROM accounts
			WHERE
				username = ?
				AND is_minister = ?;
			UPDATE accounts
			SET
				name = ?
			WHERE
				username = ?
				AND is_minister = ?
		';
		$arr = [
			$_POST['username'],
			$modify_minister,
			$_POST['new_name'],
			$_POST['username'],
			$modify_minister
		];
		if (empty(db_query($sql, $arr))) response(5, '修改权限内无对应账号！');
		
		if ($_POST['set_new_password'] == 1) {
			exist_check('new_password');
			$salt = sha1((mt_rand()));

			$sql = '
				UPDATE accounts
				SET
					salt = ?,
					salted_password_hash = ?
				WHERE
					username = ?
					AND is_minister = ?
			';
			db_query($sql, [
				$salt,
				hash('sha256', $_POST['new_password'] . $salt),
				$_POST['username'],
				$modify_minister
			]);
		}
		response();
		break;

	/* ==========================================================================
	   Module 3. Login To Business Information Manage System
	   ========================================================================== */
	case 'login':
		exist_check('username', 'password');
		if (($_POST['username'] === $super_username)
			&& (hash('sha256', $_POST['password']) === $super_password)) {
			$_SESSION['is_minister'] = $is_minister = 1;
			$_SESSION['user'] = $username = $super_username;
			
			$name = '超级管理员';
			$register_time = $update_time = '2017-10-01 00:00:00';
		} else {
			$result = db_query(
				'SELECT * FROM accounts WHERE username = ?',
				array($_POST['username'])
			);
			if (empty($result) || (
				hash('sha256', $_POST['password'] . $result[0]['salt'])
				!== $result[0]['salted_password_hash'])
			) response(6, '用户名或密码错误');
			
			$_SESSION['is_minister'] = $is_minister = $result[0]['is_minister'];
			$_SESSION['user'] = $username = $result[0]['username'];

			$name = $result[0]['name'];
			$register_time = $result[0]['register_time'];
			$update_time = $result[0]['update_time'];
		}

		echo json_encode([
			'code' => 0,
			'username' => $username,
			'name' => $name,
			'register_time' => $register_time,
			'update_time' => $update_time,
			'is_minister' => $is_minister
		]);
		break;

	/* ==========================================================================
	   Module 4. Logout
	   ========================================================================== */
	case 'logout':
		unset($_SESSION['user']);
		unset($_SESSION['is_minister']);
		response();
		break;
}
