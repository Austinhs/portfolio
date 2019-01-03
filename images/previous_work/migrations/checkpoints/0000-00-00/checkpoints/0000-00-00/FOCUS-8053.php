<?php
$datetime_type = Database::$type === 'postgres' ? 'TIMESTAMP' : 'DATETIME2';
$text_type     = Database::$type === 'postgres' ? 'TEXT' : 'VARCHAR(MAX)';
if(!Database::tableExists('updater_log')){
	Database::query("
		CREATE TABLE updater_log (
			id BIGINT PRIMARY KEY,
			staff_id BIGINT NULL,
			logged_in_as BIGINT NULL,
			action VARCHAR(255) NULL,
			svn_url {$text_type},
			start_time {$datetime_type} DEFAULT CURRENT_TIMESTAMP NOT NULL
		);
	");	
}
if(!Database::sequenceExists('updater_log_seq')) {
	Database::createSequence('updater_log_seq');
}