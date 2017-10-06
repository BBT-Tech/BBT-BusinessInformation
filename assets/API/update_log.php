<?php
require_once './config.php';

session_check();
if (!$_SESSION['is_minister']) response(1, '权限验证出错！');

$data = db_query('
	SELECT
		execute_time,
		username,
		accounts.name AS accout_name,
		is_minister,
		business_id,
		update_log.name AS business_name,
		industry,
		contact,
		address,
		willingness,
		sponsorship_content,
		charge_history,
		business_evaluation,
		remarks,
		contact_history
	FROM update_log
	JOIN accounts
		ON update_log.executer = accounts.username
	ORDER BY execute_time DESC
');
if (empty($data)) response(2, '数据库中暂无更新历史记录');

foreach ($data as &$row)
	$row['contact_history'] = str_replace('<br>', '', $row['contact_history']);

$heads = [
	'更新时间',
	'操作者用户名',
	'操作者姓名',
	'是否部长',
	'商家 ID',
	'商家名称',
	'行业',
	'联系方式',
	'商家地址',
	'合作意愿',
	'赞助内容',
	'原负责人及合作时间',
	'商家评价',
	'备注',
	'联系历史'
];
csv_export($data, $heads, 'update_log');





// Export data to csv file and auto download
// Source: https://segmentfault.com/a/1190000005366832
function csv_export($data = array(), $headlist = array(), $fileName) {
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment;filename="'.$fileName.'.csv"');
	header('Cache-Control: max-age=0');

	$fp = fopen('php://output', 'a');

	foreach ($headlist as $key => $value) {
		$headlist[$key] = iconv('utf-8', 'gbk', $value);
	}
	fputcsv($fp, $headlist);

	$count = count($data);
	for ($i = 0; $i < $count; $i++) {
		$row = $data[$i];
		foreach ($row as $key => $value) {
			$row[$key] = iconv('utf-8', 'gbk', $value);
		}
		fputcsv($fp, $row);
	}
}
