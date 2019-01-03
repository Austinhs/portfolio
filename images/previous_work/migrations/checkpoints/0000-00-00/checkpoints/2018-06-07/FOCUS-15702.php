<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::tableExists('sss_case_managers')) {
	if (!Database::sequenceExists('sss_case_managers_id_seq')) {
		Database::createSequence("sss_case_managers_id_seq");
	}

	Database::query(Database::preprocess("
		CREATE TABLE sss_case_managers (
			id BIGINT PRIMARY KEY DEFAULT {{next:sss_case_managers_id_seq}},
			student_id NUMERIC REFERENCES students(student_id),
			case_manager_id NUMERIC REFERENCES users(staff_id),
			category_id BIGINT REFERENCES sss_programs(id)
		)
	"));
}
