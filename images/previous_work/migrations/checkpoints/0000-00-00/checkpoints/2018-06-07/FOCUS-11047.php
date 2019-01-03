<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists('gl_pr_run_control_staff_misc_compensation_batches', 'facility_id')) {
		Database::createColumn('gl_pr_run_control_staff_misc_compensation_batches', 'facility_id', 'BIGINT');
		Database::createColumn('gl_pr_run_control_staff_misc_compensation_batches', 'package', 'VARCHAR', '255');
}
