<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_run_controls", "cancel_run")) {
	Database::createColumn("gl_pr_run_controls", "cancel_run", "BIGINT");
}

if (!Database::columnExists("gl_pr_run_controls", "confirm_stalled")) {
	Database::createColumn("gl_pr_run_controls", "confirm_stalled", "CHAR");
}

if (!Database::columnExists("gl_pr_run_controls", "days_worked_calculated")) {
	Database::createColumn("gl_pr_run_controls", "days_worked_calculated", "BIGINT");
}

if (!Database::columnExists("gl_pr_run_controls", "deductions_pending")) {
	Database::createColumn("gl_pr_run_controls", "deductions_pending", "BIGINT");
}

if (!Database::columnExists("gl_pr_run_controls", "deductions_total")) {
	Database::createColumn("gl_pr_run_controls", "deductions_total", "BIGINT");
}

if (!Database::columnExists("gl_pr_run_controls", "last_prelim_date")) {
	Database::createColumn("gl_pr_run_controls", "last_prelim_date", "TIMESTAMP");
}

if (!Database::columnExists("gl_pr_run_controls", "last_run_aborted")) {
	Database::createColumn("gl_pr_run_controls", "last_run_aborted", "BIGINT");
}

if (!Database::columnExists("gl_pr_run_controls", "pay_types_pending")) {
	Database::createColumn("gl_pr_run_controls", "pay_types_pending", "BIGINT");
}

if (!Database::columnExists("gl_pr_run_controls", "running_processes")) {
	Database::createColumn("gl_pr_run_controls", "running_processes", "BIGINT");
}

if (!Database::columnExists("gl_pr_run_controls", "pay_types_total")) {
	Database::createColumn("gl_pr_run_controls", "pay_types_total", "BIGINT");
}

Database::commit();

return true;
?>










