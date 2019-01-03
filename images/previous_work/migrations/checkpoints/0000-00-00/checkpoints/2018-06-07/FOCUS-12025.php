<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_history_run_wages", "transferred_to_staff_job_id")) {
	Database::createColumn("gl_pr_history_run_wages", "transferred_to_staff_job_id", "BIGINT");
}

if (!Database::columnExists("gl_pr_history_run_wages", "transferred_from_staff_job_id")) {
	Database::createColumn("gl_pr_history_run_wages", "transferred_from_staff_job_id", "BIGINT");
}

if (!Database::columnExists("gl_pr_staff_jobs_transfer", "run_id")) {
	Database::createColumn("gl_pr_staff_jobs_transfer", "run_id", "BIGINT");
}

if (!Database::columnExists("gl_pr_staff_jobs_transfer", "wages")) {
	Database::createColumn("gl_pr_staff_jobs_transfer", "wages", "numeric");
}


Database::commit();
return true;
?>
