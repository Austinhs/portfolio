<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(Database::columnExists('gl_pr_run_control_staff_misc_compensation', 'pay_rate')) {
	//grab values already existing so we can put them back after deleting the column
	$tmp = Database::get(
		"SELECT id, pay_rate
		FROM gl_pr_run_control_staff_misc_compensation
		WHERE pay_rate IS NOT NULL
	");


	Database::dropColumn('gl_pr_run_control_staff_misc_compensation', 'pay_rate');
	Database::createColumn('gl_pr_run_control_staff_misc_compensation', 'pay_rate', 'numeric', '(28,10)');
	foreach($tmp as $record) {
		Database::query(
			"UPDATE gl_pr_run_control_staff_misc_compensation
			SET pay_rate = {$record['PAY_RATE']}
			WHERE id = {$record['ID']}
		");
	}
}
