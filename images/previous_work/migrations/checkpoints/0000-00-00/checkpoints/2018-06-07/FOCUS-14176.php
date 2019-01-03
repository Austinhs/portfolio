<?php

if (!Database::tableExists('GRAD_REQUIREMENTS_SUMMARY')) {
	if (Database::$type == 'mssql') {
		Database::query(
			'CREATE TABLE GRAD_REQUIREMENTS_SUMMARY (
				GRAD_PROGRAM_ID bigint,
				STUDENT_ID bigint,
				SYEAR numeric(4),
				CALCULATED_DATE date,
				GRAD_REQ_SHORT_NAME varchar(10),
				REQUIRED numeric,
				EARNED numeric null
			)'
		);
	} else {
		Database::query(
			'CREATE TABLE GRAD_REQUIREMENTS_SUMMARY (
				GRAD_PROGRAM_ID bigint,
				STUDENT_ID bigint,
				SYEAR numeric(4),
				CALCULATED_DATE date,
				GRAD_REQ_SHORT_NAME varchar(10),
				REQUIRED numeric,
				EARNED numeric
			)'
		);
	}
}

if (!Database::indexExists('grad_requirements_summary', 'grad_requirements_summary_ind1')) {
	Database::query(
		"create index grad_requirements_summary_ind1 on grad_requirements_summary (student_id)"
	);
}

if (!Database::indexExists('grad_requirements_summary', 'grad_requirements_summary_ind2')) {
	Database::query(
		"create index grad_requirements_summary_ind2 on grad_requirements_summary (syear)"
	);
}

if (!Database::indexExists('grad_requirements_summary', 'grad_requirements_summary_ind3')) {
	Database::query(
		"create index grad_requirements_summary_ind3 on grad_requirements_summary (grad_program_id)"
	);
}

if (!Database::indexExists('grad_requirements_summary', 'grad_requirements_summary_ind4')) {
	Database::query(
		"create index grad_requirements_summary_ind4 on grad_requirements_summary (calculated_date)"
	);
}

if (!Database::indexExists('grad_requirements_summary', 'grad_requirements_summary_ind5')) {
	Database::query(
		"create index grad_requirements_summary_ind5 on grad_requirements_summary (grad_req_short_name)"
	);
}