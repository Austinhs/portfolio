<?php

// Add the "student_victims" table
if(!Database::tableExists('student_victims')) {
	$date_type = Database::$type === 'postgres' ? 'TIMESTAMP' : 'DATETIME2';
	$text_type = Database::$type === 'postgres' ? 'TEXT' : 'VARCHAR(MAX)';

	Database::query("
		CREATE TABLE student_victims (
			id BIGINT PRIMARY KEY,
			victim_student_id NUMERIC NULL,
			aggressor_student_id NUMERIC NULL,
			start_date {$date_type} NULL,
			end_date {$date_type} NULL,
			comments {$text_type} NULL,
			prevent_co_enrollment BIGINT NULL,
			deleted BIGINT NULL
		)
	");
}

// Add the "student_victims_seq" sequence
if(!Database::sequenceExists('student_victims_seq')) {
	Database::createSequence('student_victims_seq');
}

// Add foreign keys to the students table
$keys = Database::getForeignKeys('student_victims', null, false);

foreach(['victim_student_id', 'aggressor_student_id'] as $col) {
	$fkey = "student_victims_{$col}_fkey";

	if(empty($keys[$fkey])) {
		Database::query("
			ALTER TABLE
				student_victims
			ADD CONSTRAINT
				{$fkey} FOREIGN KEY ({$col}) REFERENCES students(student_id);
		");
	}
}
