<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::tableExists('gl_hr_ess_info_chg_demographic')) {
	Database::query(
		"CREATE TABLE gl_hr_ess_info_chg_demographic (
			id BIGINT PRIMARY KEY NOT NULL,
			batch_id BIGINT,
			user_column varchar(255),
			field_id varchar(255),
			field_type varchar(255),
			field_title varchar(255),
			value varchar(255)
		);
	");
}
