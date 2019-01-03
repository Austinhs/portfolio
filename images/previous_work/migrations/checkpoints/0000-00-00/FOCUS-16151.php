<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_staff_job_supplements', 'orig_total_pay')) {
	Database::createColumn('gl_pr_staff_job_supplements', 'orig_total_pay', 'numeric');
}

if(!Database::columnExists('gl_pr_staff_job_supplements', 'orig_period_pay')) {
	Database::createColumn('gl_pr_staff_job_supplements', 'orig_period_pay', 'numeric');
}


