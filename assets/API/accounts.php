<?php
session_start();
require_once('./config.php');

$_POST['operation'] = isset($_POST['operation']) ? $_POST['operation'] : 'all';
switch ($_POST['operation']) {
	/* ==========================================================================
	   Module 0. Get All Bussinesses' Information
	   ========================================================================== */
	case 'all':
		if (!isset($_SESSION['user'])) response(233, '请登录系统！');
		$sql = '
		SELECT
			account_id, username, name, is_minister, register_time, update_time
		FROM
			accounts';
		$stmt = $connect->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (empty($result)) response(7, '数据库中暂无任何账号信息');

		echo json_encode([
			'accounts' => array_values($result),
			'code' => 0
		]);
		break;

	/* ==========================================================================
	   Module 1. Create A New Account
	   ========================================================================== */
	case 'create':
		if (!isset($_SESSION['user'])) response(233, '请登录系统！');
		if (!(($_SESSION['user'] == $super_username)
			|| ($_SESSION['user'] == 'minister')))
			response(1, '权限验证出错！');
		existCheck('username', 'name', 'password');

		$sql = 'SELECT * FROM accounts WHERE username = ?';
		$stmt = $connect->prepare($sql);
		$stmt->execute([$_POST['username']]);
		$result = $stmt->fetchColumn();
		if (($result !== false) || ($_POST['username'] == $super_username))
			response(2, '该用户名已存在，请更换');

		$salt = sha1((mt_rand()));
		$sql = '
		INSERT INTO accounts
			(username, name, salt, salted_password_hash, is_minister)
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
		if ($stmt->fetchColumn() === false) response(0);
		response(3, '新建账号失败，请联系管理员');
		break;

	/* ==========================================================================
	   Module 2. Modify An Account's Name And Password
	   ========================================================================== */
	case 'modify':
		if (!isset($_SESSION['user'])) response(233, '请登录系统！');
		if (!($_SESSION['user'] == $super_username || $_SESSION['user'] == 'minister'))
			response(4, '权限验证出错！');
		existCheck('username', 'new_name', 'set_new_password');
		$modify_minister = $_SESSION['user'] == $super_username ? 1 : 0;

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
			AND is_minister = ?';
		$stmt = $connect->prepare($sql);
		$stmt->execute([
			$_POST['username'],
			$modify_minister,
			$_POST['new_name'],
			$_POST['username'],
			$modify_minister
		]);

		if ($stmt->fetchColumn() !== false) {
			if ($_POST['set_new_password'] == 1) {
				existCheck('new_password');
				$salt = sha1((mt_rand()));

				$stmt = $connect->prepare('
					SELECT * FROM accounts
					WHERE
						username = ?
						AND is_minister = ?;
					UPDATE accounts
					SET
						salt = ?,
						salted_password_hash = ?
					WHERE
						username = ?
						AND is_minister = ?');
				$stmt->execute([
					$_POST['username'],
					$modify_minister,
					$salt,
					hash('sha256', $_POST['new_password'] . $salt),
					$_POST['username'],
					$modify_minister
				]);
				if ($stmt->fetchColumn() !== false) response(0);
			}
			response(0);
		}
		response(5, '修改权限内无对应账号！');
		break;

	/* ==========================================================================
	   Module 3. Login To Business Information Manage System
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
			SELECT * FROM accounts WHERE username = ?';
			$stmt = $connect->prepare($sql);
			$stmt->execute([$_POST['username']]);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if (($result === false) || (
				hash('sha256', $_POST['password'] . $result['salt'])
				!== $result['salted_password_hash'])
			) response(6, '用户名或密码错误');
			
			$_SESSION['user'] = $result['is_minister'] ?
				'minister' : $result['username'];
			$name = $result['name'];
			$register_time = $result['register_time'];
			$update_time = $result['update_time'];
			$is_minister = $result['is_minister'];
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
	   Module 4. Logout
	   ========================================================================== */
	case 'logout':
		unset($_SESSION['user']);
		response(0);
		break;
}
