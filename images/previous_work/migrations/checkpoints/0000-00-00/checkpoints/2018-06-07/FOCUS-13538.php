<?php
Database::begin();

if (!Database::tableExists("exception_log")) {
	$timestamp = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";
	$sql       =
		"CREATE TABLE exception_log (
			id BIGINT PRIMARY KEY,
			message TEXT,
			stack_trace TEXT,
			\"file\" VARCHAR(255),
			\"line\" INT,
			error_type VARCHAR(20),
			source_type VARCHAR(20),
			module VARCHAR(255),
			package VARCHAR(255),
			user_id BIGINT,
			created_at {$timestamp}
		)";

	Database::query($sql);

	$sql =
		"CREATE SEQUENCE 
			exception_log_seq";

	Database::query($sql);

	$sql =
		"UPDATE
			exception_log
		SET
			id = {{next:exception_log_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);
}

Database::commit();
return true;
?>