<?php
session_start();
require_once('./config.php');

// if (!isset($_SESSION['user'])) response(233, '请先登录系统！');
$_POST['operation'] = isset($_POST['operation']) ? $_POST['operation'] : 'all';

switch ($_POST['operation']) {
	/* ==========================================================================
	   Module 0. Get All Bussinesses' Information
	   ========================================================================== */
	case 'all':
		break;

	/* ==========================================================================
	   Module 1. Search A Bussiness Information Via Keyword
	   ========================================================================== */
	case 'search':
		break;

	/* ==========================================================================
	   Module 2. Add A Bussiness Information
	   ========================================================================== */
	case 'add':
		existCheck('name', 'industry', 'contact', 'address', 'willingness', 'sponsorship_content', 'charge_history', 'business_evaluation', 'remarks', 'contact_history');
		$_POST['is_contacted'] = isset($_POST['is_contacted']) ? 1 : 0;

		$sql = '
		INSERT INTO `businesses`
			(`name`, `industry`, `contact`, `address`, `willingness`, `sponsorship_content`, `charge_history`, `business_evaluation`, `remarks`, `is_contacted`, `contact_history`)
		VALUES
			(?,?,?,?,?,?,?,?,?,?,?)';
		$stmt = $connect->prepare($sql);
		$stmt->execute([
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
		if (empty($stmt->fetchAll(PDO::FETCH_ASSOC)))
			response(0);
		else
			response(3, '写入数据库时发生错误，请联系管理员');
		break;

	/* ==========================================================================
	   Module 3. Update A Bussiness Information
	   ========================================================================== */
	case 'update':
		break;
}
