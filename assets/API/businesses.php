<?php
require_once('./config.php');

session_check();
$_POST['operation'] = isset($_POST['operation']) ? $_POST['operation'] : 'all';

switch ($_POST['operation']) {
	/* ==========================================================================
	   Module 0. Get All Bussinesses' Information
	   ========================================================================== */
	case 'all':
		$result = db_query('SELECT * FROM businesses');
		if (empty($result)) response(1, '数据库中暂无商家信息');

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
		exist_check('name', 'industry', 'contact', 'address', 'willingness', 'sponsorship_content', 'charge_history', 'business_evaluation', 'remarks', 'is_contacted', 'contact_history');

		$sql = '
			INSERT INTO businesses
				(name, industry, contact, address, willingness, sponsorship_content, charge_history, business_evaluation, remarks, is_contacted, contact_history)
			VALUES (?,?,?,?,?,?,?,?,?,?,?)
		';
		$arr = [
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
		];
		db_query($sql, $arr);
		response();
		break;

	/* ==========================================================================
	   Module 3. Update A Bussiness Information
	   ========================================================================== */
	case 'update':
		exist_check('name', 'industry', 'contact', 'address', 'willingness', 'sponsorship_content', 'charge_history', 'business_evaluation', 'remarks', 'is_contacted', 'contact_history', 'business_id');

		$sql = '
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

		db_query($sql, $arr);
		if ($_POST['is_contacted'] == 0 && $_SESSION['user'] == 'minister') {
			$sql = '
				UPDATE businesses
				SET
					is_contacted = 0,
					contact_history = ""
				WHERE business_id = ?
			';
			db_query($sql, [$_POST['business_id']]);
		}
		response();
		break;
}
