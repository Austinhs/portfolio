<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_run_control_staff_misc_compensation", "leave_payout")) {
	Database::createColumn("gl_pr_run_control_staff_misc_compensation", "leave_payout", "INT");
}

if (!Database::columnExists("gl_pr_history_run_wages", "leave_payout")) {
	Database::createColumn("gl_pr_history_run_wages", "leave_payout", "INT");
}

if (!Database::columnExists("gl_pr_retirement_adjustments", "run_id")) {
	Database::createColumn("gl_pr_retirement_adjustments", "run_id", "BIGINT");
}


Database::commit();
return true;
?>
