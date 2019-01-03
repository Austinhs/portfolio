<?php

if (!Database::columnExists('schedule', 'reauthorization_section')) {
	Database::createColumn('schedule', 'reauthorization_section', 'varchar');
}

if (!Database::columnExists('schedule', 'reauthorization_length')) {
	Database::createColumn('schedule', 'reauthorization_length', 'varchar');
}

if (!Database::columnExists('schedule', 'reauthorization_schedule_id')) {
	Database::createColumn('schedule', 'reauthorization_schedule_id', 'numeric');
}

if (!Database::columnExists('schedule', 'reauthorization_invoice_id')) {
	Database::createColumn('schedule', 'reauthorization_invoice_id', 'numeric');
}

if (!Database::columnExists('schedule', 'reauthorized')) {
	Database::createColumn('schedule', 'reauthorized', 'varchar', '1');
}

if (!Database::sequenceExists('reauthorization_completed_seq')) {
	Database::createSequence('reauthorization_completed_seq');
}

if (!Database::tableExists('reauthorization_completed') && Database::sequenceExists('reauthorization_completed_seq')) {

	$table_query = Database::preprocess("
		CREATE TABLE reauthorization_completed (
			id BIGINT NOT NULL PRIMARY KEY DEFAULT {{next:reauthorization_completed_seq}},
			completed VARCHAR(1) NULL,
			course_period_id NUMERIC NOT NULL,
			staff_id NUMERIC NOT NULL,
			FOREIGN KEY (staff_id) REFERENCES users(staff_id),
			FOREIGN KEY (course_period_id) REFERENCES course_periods(course_period_id)
		)
	");

	Database::query($table_query);
}

if (!Database::columnExists('school_progress_periods', 'registration_start_date')) {
	Database::createColumn('school_progress_periods', 'registration_start_date', 'date');
}

if (!Database::columnExists('school_progress_periods', 'registration_end_date')) {
	Database::createColumn('school_progress_periods', 'registration_end_date', 'date');
}