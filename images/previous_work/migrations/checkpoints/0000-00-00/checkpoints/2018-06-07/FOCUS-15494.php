<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_staff_deductions", "vendor_id")) {
	Database::createColumn("gl_pr_staff_deductions", "vendor_id", "bigint");
}

if (!Database::columnExists("gl_pr_history_run_deductions", "vendor_id")) {
	Database::createColumn("gl_pr_history_run_deductions", "vendor_id", "bigint");
}

Database::commit();
return true;
?>
