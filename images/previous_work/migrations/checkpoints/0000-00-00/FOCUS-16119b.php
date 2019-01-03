<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_history_run_leave_adjustments', 'package')) {
	Database::createColumn('gl_pr_history_run_leave_adjustments', 'package', 'varchar', '255');
}
