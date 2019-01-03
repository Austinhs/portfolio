<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_run_control_auto_adjustment", "staff_supplement_id")) {
	Database::createColumn("gl_pr_run_control_auto_adjustment", "staff_supplement_id", "BIGINT");
}

if (!Database::columnExists("gl_pr_run_control_auto_adjustment", "supplement_annualized")) {
	Database::createColumn("gl_pr_run_control_auto_adjustment", "supplement_annualized", "numeric(28,10)");
}

if (!Database::columnExists("gl_pr_run_control_auto_adjustment", "supplement_period_pay")) {
	Database::createColumn("gl_pr_run_control_auto_adjustment", "supplement_period_pay", "numeric(28,10)");
}

if (!Database::columnExists("gl_pr_run_control_auto_adjustment", "supplement_total_pay")) {
	Database::createColumn("gl_pr_run_control_auto_adjustment", "supplement_total_pay", "numeric(28,10)");
}

Database::commit();
return true;
?>
