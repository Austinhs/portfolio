<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_run_control_dockage_owed', 'package')) {
	Database::createColumn('gl_pr_run_control_dockage_owed', 'package', 'varchar', '255');
}

if(!Database::columnExists('gl_pr_history_run_leave_adjustments', 'date_created')) {
	Database::createColumn('gl_pr_history_run_leave_adjustments', 'date_created', 'timestamp');
}

if(!Database::columnExists('gl_pr_history_run_leave_adjustments', 'package')) {
	Database::createColumn('gl_pr_history_run_leave_adjustments', 'package', 'varchar', '255');
}
