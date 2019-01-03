<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::tableExists('gl_hr_ess_info_chg_leave')) {
	Database::query(
		"CREATE TABLE gl_hr_ess_info_chg_leave (
			id BIGINT PRIMARY KEY,
			batch_id BIGINT,
			staff_id BIGINT,
			staff_job_id BIGINT,
			bucket_group_id BIGINT,
			bucket_reason_id BIGINT,
			pay_type_id BIGINT,
			facility_id BIGINT
		)
	");

	Database::createColumn("gl_hr_ess_info_chg_leave", "hours_per_day", "NUMERIC");
	Database::createColumn("gl_hr_ess_info_chg_leave", "from_date", "timestamp");
	Database::createColumn("gl_hr_ess_info_chg_leave", "to_date", "timestamp");
}
