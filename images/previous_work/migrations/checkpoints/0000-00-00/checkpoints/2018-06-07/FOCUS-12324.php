<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_pr_run_controls", "skip_deductions")) {
	Database::begin();
	Database::createColumn("gl_pr_run_controls", "skip_deductions", "BIGINT");
	Database::commit();
}

return true;
?>
