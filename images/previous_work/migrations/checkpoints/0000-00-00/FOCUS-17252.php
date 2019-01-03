<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_pr_staff_jobs", "temp")) {
	Database::createColumn("gl_pr_staff_jobs", "temp", "BIGINT");
}

return true;
