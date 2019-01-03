<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if(!Database::columnExists('gl_pr_staff_leave_requests', 'school_department_facility')) {
	Database::createColumn('gl_pr_staff_leave_requests', 'school_department_facility', 'BIGINT');
}

Database::commit();
