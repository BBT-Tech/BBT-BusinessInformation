<?php
session_start();
require_once('./config.php');

$_POST['operation'] = isset($_POST['operation']) ? $_POST['operation'] : 'logout';

switch ($_POST['operation']) {
	/* ==========================================================================
	   Module 0. Create A New Account
	   ========================================================================== */
	case 'create':
		if (!isset($_SESSION['user'])) response(233, '请登录系统！');
		if (!($_SESSION['user'] == $super_username || $_SESSION['user'] == 'minister'))
			response(1, '权限验证出错！');
		existCheck('username', 'name', 'password');

		$sql = 'SELECT * FROM `accounts` WHERE username = ?';
		$stmt = $connect->prepare($sql);
		$stmt->execute([$_POST['username']]);
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!empty($result)) response(2, '该用户名已存在，请更换');

		$salt = sha1((mt_rand()));
		$sql = '
		INSERT INTO `accounts`
			(`username`, `name`, `salt`, `salted_password_hash`, `is_minister`)
		VALUES
			(?,?,?,?,?)';
		$stmt = $connect->prepare($sql);
		$stmt->execute([
			$_POST['username'],
			$_POST['name'],
			$salt,
			hash('sha256', $_POST['password'] . $salt),
			($_SESSION['user'] == $super_username ? 1 : 0)
		]);
		if (empty($stmt->fetchAll(PDO::FETCH_ASSOC))) response(0);
		response(3, '新建账号失败，请联系管理员');
		break;

	/* ==========================================================================
	   Module 1. Modify An Account's Name And Password
	   ========================================================================== */
	case 'modify':
		if (!isset($_SESSION['user'])) response(233, '请登录系统！');
		if (!($_SESSION['user'] == $super_username || $_SESSION['user'] == 'minister'))
			response(4, '权限验证出错！');
		existCheck('username', 'new_name', 'set_new_password');

		$sql = '
		UPDATE `accounts`
		SET
			`name` = ?
		WHERE
			`username` = ?
			AND `is_minister` = ?';
		$stmt = $connect->prepare($sql);
		$stmt->execute([
			$_POST['new_name'],
			$_POST['username'],
			($_SESSION['user'] == $super_username ? 1 : 0)
		]);

		if (empty($stmt->fetchAll(PDO::FETCH_ASSOC))) {
			if ($_POST['set_new_password'] == 1) {
				existCheck('new_password');
				$salt = sha1((mt_rand()));

				$stmt = $connect->prepare('
					UPDATE `accounts`
					SET
						`salt` = ?,
						`salted_password_hash` = ?
					WHERE
						`username` = ?
						AND `is_minister` = ?');
				$stmt->execute([
					$salt,
					hash('sha256', $_POST['new_password'] . $salt),
					$_POST['username'],
					($_SESSION['user'] == $super_username ? 1 : 0)
				]);
				$stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			response(0);
		}
		response(5, '修改账号失败，请联系管理员');
		break;

	/* ==========================================================================
	   Module 2. Login To Business Information Manage System
	   ========================================================================== */
	case 'login':
		existCheck('username', 'password');
		if ($_POST['username'] === $super_username
			&& hash('sha256', $_POST['password']) === $super_password) {
			$_SESSION['user'] = $super_username;
			$name = '超级管理员';
			$register_time = '2017-10-01 00:00:00';
			$update_time =   '2017-10-01 00:00:00';
			$is_minister = 1;
		} else {
			$sql = '
			SELECT * FROM `accounts` WHERE username = ?';
			$stmt = $connect->prepare($sql);
			$stmt->execute([$_POST['username']]);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (empty($result) || (
				hash('sha256', $_POST['password'] . $result[0]['salt'])
				!== $result[0]['salted_password_hash'])
			) response(6, '用户名或密码错误');
			
			$_SESSION['user'] = $result[0]['is_minister'] ? 'minister' : 'member';
			$name = $result[0]['name'];
			$register_time = $result[0]['register_time'];
			$update_time = $result[0]['update_time'];
			$is_minister = $result[0]['is_minister'];
		}

		echo json_encode([
			'code' => 0,
			'name' => $name,
			'register_time' => $register_time,
			'update_time' => $update_time,
			'is_minister' => $is_minister
		]);
		break;

	/* ==========================================================================
	   Module 3. Logout
	   ========================================================================== */
	case 'logout':
		unset($_SESSION['user']);
		response(0);
		break;
}
