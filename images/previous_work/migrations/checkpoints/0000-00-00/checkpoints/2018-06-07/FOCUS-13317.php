<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_zero_report_activity_type")) {
	$sql =
		"CREATE TABLE gl_zero_report_activity_type (
			id BIGINT PRIMARY KEY,
			name VARCHAR(255),
			append_vendor INT,
			sort INT
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_zero_report_activity_type_criteria")) {
	$sql =
		"CREATE TABLE gl_zero_report_activity_type_criteria (
			id BIGINT PRIMARY KEY,
			activity_type_id BIGINT,
			type VARCHAR(255),
			value TEXT,
			miscellaneous INT
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_zero_report_object")) {
	$sql =
		"CREATE TABLE gl_zero_report_object (
			id BIGINT PRIMARY KEY,
			code VARCHAR(255)
		)";

	Database::query($sql);
}

Database::commit();
return true;
?>