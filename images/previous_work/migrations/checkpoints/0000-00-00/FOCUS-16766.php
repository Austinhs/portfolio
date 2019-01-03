<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}
Database::begin();

if(!Database::columnExists('gl_pr_history_run_wages', 'staff_job_supplement_id')) {
	Database::createColumn('gl_pr_history_run_wages', 'staff_job_supplement_id', 'bigint');


	Database::query("
		update gl_pr_history_run_wages
		set staff_job_supplement_id = staff_supplement_id
		where staff_supplement_id is not null
	");

	Database::query("
		update gl_pr_history_run_wages
		set staff_job_supplement_id =
		(
			select adj.supplement_id
			from gl_pr_run_control_adjustments adj
			where adj.id = gl_pr_history_run_wages.adjustment_id
		)
		where staff_supplement_id is null
		and staff_job_supplement_id is null
		and adjustment_id is not null
		and supplement_id is not null
	");
}

if(!Database::columnExists('gl_pr_run_control_staff_misc_compensation', 'staff_job_supplement_id')) {
	Database::createColumn('gl_pr_run_control_staff_misc_compensation', 'staff_job_supplement_id', 'bigint');
}

Database::commit();

return true;
