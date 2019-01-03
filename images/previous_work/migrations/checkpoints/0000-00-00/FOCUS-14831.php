<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_pr_run_control_deductions_skip")) {
	$sql =
		"CREATE TABLE gl_pr_run_control_deductions_skip (
			id BIGINT PRIMARY KEY,
			deduction_class_id BIGINT,
			fyear INT,
			run_id BIGINT,
			subclass varchar(100)
		)";

	Database::query($sql);

	Database::query("create index gl_pr_run_control_deductions_skip_run_id on gl_pr_run_control_deductions_skip (run_id)");
}

if (!Database::columnExists('gl_pr_history_run_deductions', 'adj_payback_deduction')) {
	Database::createColumn('gl_pr_history_run_deductions', 'adj_payback_deduction', 'int');
}

// if (!Database::columnExists('gl_pr_history_run_deductions', 'adj_social_security')) {
// 	Database::createColumn('gl_pr_history_run_deductions', 'adj_social_security', 'char(1)');
// }

// if (!Database::columnExists('gl_pr_history_run_deductions', 'adj_retirement')) {
// 	Database::createColumn('gl_pr_history_run_deductions', 'adj_retirement', 'char(1)');
// }

// if (!Database::columnExists('gl_pr_history_run_deductions', 'adj_insurance')) {
// 	Database::createColumn('gl_pr_history_run_deductions', 'adj_insurance', 'char(1)');
// }

// if (!Database::columnExists('gl_pr_history_run_deductions', 'adj_taxable')) {
// 	Database::createColumn('gl_pr_history_run_deductions', 'adj_taxable', 'char(1)');
// }

Database::commit();
return true;
?>
