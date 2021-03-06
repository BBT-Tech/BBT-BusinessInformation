<?php
require_once './config.php';

session_check();
$_POST['operation'] = isset($_POST['operation']) ? $_POST['operation'] : 'all';
switch ($_POST['operation']) {
	/* ==========================================================================
	   Module 0. Get All Bussinesses' Information
	   ========================================================================== */
	case 'all':
		$result = db_query('SELECT * FROM businesses');
		if (empty($result)) response(1, '数据库中暂无任何商家信息');

		echo json_encode([
			'code' => 0,
			'businesses' => array_values($result)
		]);
		break;

	/* ==========================================================================
	   Module 1. Search A Bussiness Information Via Keyword
	   ========================================================================== */
	case 'search':
		$result = db_query('
			SELECT * FROM businesses
			WHERE
				name LIKE ?
				OR industry LIKE ?
				OR contact LIKE ?
				OR address LIKE ?
				OR willingness LIKE ?
				OR sponsorship_content LIKE ?
				OR charge_history LIKE ?
				OR business_evaluation LIKE ?
				OR remarks LIKE ?
				OR contact_history LIKE ?',
			array_pad([], 10, '%' . $_POST['keyword'] . '%')
		);
		if (empty($result)) response(2, '未找到符合条件的商家信息，换个关键词试试吧~');

		echo json_encode([
			'code' => 0,
			'businesses' => array_values($result)
		]);
		break;

	/* ==========================================================================
	   Module 2. Add A Bussiness Information
	   ========================================================================== */
	case 'add':
		if ($_SESSION['user'] == $super_username) response(3, '权限验证出错！');
		exist_check('name', 'industry', 'contact', 'address', 'willingness', 'sponsorship_content', 'charge_history', 'business_evaluation', 'remarks', 'is_contacted', 'contact_history');

		$sql = '
			INSERT INTO businesses
				(name, industry, contact, address, willingness, sponsorship_content, charge_history, business_evaluation, remarks, is_contacted, contact_history)
			VALUES (?,?,?,?,?,?,?,?,?,?,?)
		';
		db_query($sql, [
			$_POST['name'],
			$_POST['industry'],
			$_POST['contact'],
			$_POST['address'],
			$_POST['willingness'],
			$_POST['sponsorship_content'],
			$_POST['charge_history'],
			$_POST['business_evaluation'],
			$_POST['remarks'],
			$_POST['is_contacted'],
			$_POST['contact_history']
		]);
		response();
		break;

	/* ==========================================================================
	   Module 3. Update A Bussiness Information
	   ========================================================================== */
	case 'update':
		if ($_SESSION['user'] == $super_username) response(4, '权限验证出错！');
		exist_check('name', 'industry', 'contact', 'address', 'willingness', 'sponsorship_content', 'charge_history', 'business_evaluation', 'remarks', 'is_contacted', 'contact_history', 'business_id');

		$sql = '
			SELECT * FROM businesses
			WHERE business_id = ?;
			UPDATE businesses
			SET
				name = ?,
				industry = ?,
				contact = ?,
				address = ?,
				willingness = ?,
				sponsorship_content = ?,
				charge_history = ?,
				business_evaluation = ?,
				remarks = ?,
				is_contacted = is_contacted OR ?,
				contact_history = CONCAT(contact_history, ?)
			WHERE business_id = ?
			';
		$arr = [
			$_POST['business_id'],
			$_POST['name'],
			$_POST['industry'],
			$_POST['contact'],
			$_POST['address'],
			$_POST['willingness'],
			$_POST['sponsorship_content'],
			$_POST['charge_history'],
			$_POST['business_evaluation'],
			$_POST['remarks'],
			$_POST['is_contacted'],
			$_POST['contact_history'],
			$_POST['business_id']
		];

		if (empty(db_query($sql, $arr))) response(5, '请求错误，数据库中无对应商家');
		if (($_POST['is_contacted'] == 0) && $_SESSION['is_minister']) {
			$sql = '
				UPDATE businesses
				SET
					is_contacted = 0,
					contact_history = ""
				WHERE business_id = ?
			';
			db_query($sql, array($_POST['business_id']));
		}

		//Record An Update Log
		$max_rows = 3000;
		$rows = db_query('SELECT COUNT(*) AS rows FROM update_log')[0]['rows'];
		$sql = $rows < $max_rows ?
			'
			INSERT INTO update_log
				(executer, business_id, name, industry, contact, address, willingness, sponsorship_content, charge_history, business_evaluation, remarks, contact_history)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
			' :
			'
			UPDATE update_log
			SET
				executer = ?,
				business_id = ?,
				name = ?,
				industry = ?,
				contact = ?,
				address = ?,
				willingness = ?,
				sponsorship_content = ?,
				charge_history = ?,
				business_evaluation = ?,
				remarks = ?,
				contact_history = ?
			WHERE
				log_id =
					(
						SELECT MIN(log_id) FROM
						(
							SELECT * FROM update_log
							WHERE
								execute_time =
								(SELECT MIN(execute_time) FROM update_log)
						)
						AS this
					)
			';
		db_query($sql, [
			$_SESSION['user'],
			$_POST['business_id'],
			$_POST['name'],
			$_POST['industry'],
			$_POST['contact'],
			$_POST['address'],
			$_POST['willingness'],
			$_POST['sponsorship_content'],
			$_POST['charge_history'],
			$_POST['business_evaluation'],
			$_POST['remarks'],
			$_POST['contact_history']
		]);
		response();
		break;
}
