<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}


if(!Database::columnExists('gl_pr_staff_leave_requests', 'original_hours_off_per_day')) {
	Database::createColumn('gl_pr_staff_leave_requests', 'original_hours_off_per_day', 'numeric', '(28,10)');
}
