<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_pr_staff_jobs", "eeo_group")) {
	Database::createColumn("gl_pr_staff_jobs", "eeo_group", "CHAR(1)");
}

Database::query("

	update gl_pr_staff_jobs
	set eeo_group = (
	".db_limit("
				select
						case
							when eeo.code between '21' and '43' then 'I'
							when eeo.code between '01' and '20' then 'A'
							else 'S'
						end as eeo_group
					from gl_pr_staff_job_positions sjp
					join gl_pr_positions p on p.id = sjp.position_id
					join gl_pr_jobs_local j ON (j.id = sjp.job_id)
					join gl_pr_jobs_state js on js.id = j.job_code_state_id
					join gl_pr_equal_employment_opportunity_numbers eeo on eeo.id = j.employee_equal_opportunity_id
					where js.local_id <> '99998'
					and sjp.staff_job_id = gl_pr_staff_jobs.id
					order by sjp.fyear desc
		",1)."
	)
	where eeo_group is null
");

return true;
