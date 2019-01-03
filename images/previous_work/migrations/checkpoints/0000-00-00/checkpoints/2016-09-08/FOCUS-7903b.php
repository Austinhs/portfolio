<?php

// Add info_change_requests table
$text_type = Database::$type === 'mssql' ? 'VARCHAR(MAX)' : 'TEXT';
$date_type = Database::$type === 'mssql' ? 'DATETIME2' : 'TIMESTAMP';

$table = "info_change_requests";
$seq   = "{$table}_seq";

if(!Database::tableExists($table)) {
	$create_sql = "
		CREATE TABLE {$table} (
			id BIGINT PRIMARY KEY,
			status VARCHAR(255) NOT NULL,
			school_id BIGINT NOT NULL,
			request_date {$date_type} NOT NULL,
			requester_id BIGINT NOT NULL,
			requester_class VARCHAR(255) NOT NULL,
			target_id BIGINT NOT NULL,
			target_class VARCHAR(255) NOT NULL,
			change {$text_type} NOT NULL
		)
	";

	Database::query($create_sql);
}

if(!Database::sequenceExists($seq)) {
	Database::createSequence($seq);
}
