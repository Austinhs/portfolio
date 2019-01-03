<?php
Database::begin();

if (!Database::tableExists("intro_js_log")) {
	$sql =
		"CREATE TABLE intro_js_log (
			id BIGINT PRIMARY KEY,
			user_id BIGINT,
			module VARCHAR(255),
			step_key VARCHAR(255),

			UNIQUE(user_id, module, step_key)
		)";

	Database::query($sql);

	$sql =
		"CREATE SEQUENCE 
			intro_js_log_seq
		START WITH
			1";

	Database::query($sql);

	$sql =
		"UPDATE
			intro_js_log
		SET
			id = {{next:intro_js_log_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);
}

Database::commit();
return true;
?>