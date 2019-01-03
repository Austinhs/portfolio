<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_pr_staff_job_position_allocations")) {
	$sql =
		"CREATE TABLE gl_pr_staff_job_position_allocations (
			id BIGINT PRIMARY KEY
		)";

	Database::query($sql);

	Database::createColumn("gl_pr_staff_job_position_allocations", "deleted", "int");
	Database::createColumn("gl_pr_staff_job_position_allocations", "staff_id", "bigint");
	Database::createColumn("gl_pr_staff_job_position_allocations", "staff_job_id", "bigint");
	Database::createColumn("gl_pr_staff_job_position_allocations", "staff_position_id", "bigint");
	Database::createColumn("gl_pr_staff_job_position_allocations", "position_id", "bigint");
	Database::createColumn("gl_pr_staff_job_position_allocations", "contract_year", "int");
	Database::createColumn("gl_pr_staff_job_position_allocations", "accounting_strip_hash", "varchar(255)");
	Database::createColumn("gl_pr_staff_job_position_allocations", "accounting_strip_id", "bigint");
	Database::createColumn("gl_pr_staff_job_position_allocations", "allocation_percent", "NUMERIC(28,10)");
	Database::createColumn("gl_pr_staff_job_position_allocations", "date_start", "TIMESTAMP");
	Database::createColumn("gl_pr_staff_job_position_allocations", "date_end", "TIMESTAMP");
	Database::createColumn("gl_pr_staff_job_position_allocations", "last_modified_by", "bigint");
	Database::createColumn("gl_pr_staff_job_position_allocations", "last_modified_date", "TIMESTAMP");
	Database::createColumn("gl_pr_staff_job_position_allocations", "last_modified_time", "TIMESTAMP");

	if (!Database::indexExists("gl_pr_staff_job_position_allocations", "staff_position_allo_1")) {
		Database::query("CREATE INDEX staff_position_allo_1 ON gl_pr_staff_job_position_allocations(staff_job_id,contract_year)");
	}

	if (!Database::indexExists("gl_pr_staff_job_position_allocations", "staff_position_allo_2")) {
		Database::query("CREATE INDEX staff_position_allo_2 ON gl_pr_staff_job_position_allocations(staff_position_id,contract_year)");
	}

	if (!Database::columnExists("gl_pr_staff_job_allocations", "contract_year")) {
		Database::createColumn("gl_pr_staff_job_allocations", "contract_year", "int");
	}
}

if (!Database::columnExists("gl_pr_staff_job_positions", "override_allocations")) {
	Database::createColumn("gl_pr_staff_job_positions", "override_allocations", "int");
}


if (!Database::columnExists("gl_ba_check_totals", "medicare_qual")) {
	Database::createColumn("gl_ba_check_totals", "medicare_qual", "NUMERIC(28,10)");
}

if (!Database::columnExists("gl_ba_check_totals", "social_security_qual")) {
	Database::createColumn("gl_ba_check_totals", "social_security_qual", "NUMERIC(28,10)");
}

if (!Database::columnExists("gl_ba_check_totals", "insurance_qual")) {
	Database::createColumn("gl_ba_check_totals", "insurance_qual", "NUMERIC(28,10)");
}

if (!Database::columnExists("gl_ba_check_totals", "retirement_qual")) {
	Database::createColumn("gl_ba_check_totals", "retirement_qual", "NUMERIC(28,10)");
}







Database::commit();
return true;
?>

