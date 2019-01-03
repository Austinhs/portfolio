<?php

$text_type = Database::$type === 'mssql' ? 'VARCHAR(MAX)' : 'TEXT';
$date_type = Database::$type === 'mssql' ? 'DATETIME2' : 'TIMESTAMP';

if(!Database::tableExists('tasks')) {
	Database::query("
		CREATE TABLE tasks (
			id BIGINT PRIMARY KEY,
			title {$text_type} NULL,
			username VARCHAR(255) NULL,
			pid BIGINT NOT NULL,
			path {$text_type} NOT NULL,
			started {$date_type} NOT NULL DEFAULT CURRENT_TIMESTAMP,
			exited {$date_type} NULL
		)
	");
}

if(!Database::sequenceExists('tasks_seq')) {
	Database::createSequence('tasks_seq');
}

if(!Database::tableExists('task_events')) {
	$sql = Database::preprocess("
		CREATE TABLE task_events (
			id BIGINT PRIMARY KEY,
			task_id BIGINT NOT NULL{{postgres: REFERENCES tasks (id)}},
			time {$date_type} NOT NULL DEFAULT CURRENT_TIMESTAMP,
			event {$text_type} NOT NULL,
			data {$text_type} NULL
			{{mssql:
				CONSTRAINT task_events_task_id_fkey
				FOREIGN KEY (task_id)
				REFERENCES tasks (id)
			}}
		)
	");

	Database::query($sql);
}

if(!Database::sequenceExists('task_events_seq')) {
	Database::createSequence('task_events_seq');
}
