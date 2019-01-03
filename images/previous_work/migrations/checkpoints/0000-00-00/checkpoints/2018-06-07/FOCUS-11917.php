<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pos_deferral", "created_by")) {
	Database::createColumn("gl_pos_deferral", "created_by", "BIGINT");

	$sql =
		"UPDATE
			gl_pos_deferral
		SET
			created_by = 
				(
					SELECT
						MAX(COALESCE(user_id, logged_in_id))
					FROM
						database_object_log
					WHERE
						record_id = gl_pos_deferral.id AND
						record_class = 'POSDeferral' AND
						action = 'INSERT'
				)";

	Database::query($sql);
}

if (!Database::columnExists("gl_pos_deferral", "created_date")) {
	Database::createColumn("gl_pos_deferral", "created_date", "TIMESTAMP");

	$sql =
		"UPDATE
			gl_pos_deferral
		SET
			created_date = 
				(
					SELECT
						MIN(log_time)
					FROM
						database_object_log
					WHERE
						record_id = gl_pos_deferral.id AND
						record_class = 'POSDeferral' AND
						action = 'INSERT'
				)";

	Database::query($sql);
}

Database::commit();
return true;
?>