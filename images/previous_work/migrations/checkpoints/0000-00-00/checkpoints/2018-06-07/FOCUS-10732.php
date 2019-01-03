<?php

if(Database::tableExists('gl_pr_history_run_wages')) {
	if(!Database::columnExists('gl_pr_history_run_wages', 'job_wage_id')) {
		Database::createColumn('gl_pr_history_run_wages', 'job_wage_id', 'bigint');
	}

	if(!Database::indexExists('gl_pr_history_run_wages', 'gl_pr_history_run_wages_job_wage_id')) {
		Database::query("
			CREATE INDEX
				gl_pr_history_run_wages_job_wage_id
			ON
				gl_pr_history_run_wages(job_wage_id)
		");
	}
}
else {
	return false;
}
