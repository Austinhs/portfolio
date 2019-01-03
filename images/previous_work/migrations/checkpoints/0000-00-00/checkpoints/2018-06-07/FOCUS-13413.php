<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (Database::$type === 'postgres') {
	$text      = 'text';
	$timestamp = 'timestamp without time zone';
} else {
	$text      = 'varchar(max)';
	$timestamp = 'datetime2(6)';
}

Database::query("DELETE FROM permission WHERE \"key\" LIKE 'sss/interpreters%'");
Database::query("DELETE FROM permission WHERE \"key\" LIKE 'sss/reserve%'");
Database::query("DELETE FROM permission WHERE \"key\" LIKE 'sss/district-reserve%'");
Database::query("DELETE FROM permission WHERE \"key\" LIKE 'sss/caseload%'");
Database::query("DELETE FROM permission WHERE \"key\" LIKE 'sss/evaluator-caseload%'");
Database::query("DELETE FROM permission WHERE \"key\" LIKE 'sss/evaluation-referrals%'");
Database::query("DROP TABLE sss_teacher_caseload");
// TODO: remove this table
// Database::query("DROP TABLE sss_students");
Database::query("DROP TABLE sss_reserve");

// TODO: these can be migrated to district reports page
// Database::query("DROP TABLE sss_reports");
Database::query("DROP TABLE sss_interpreters");
Database::query("DROP TABLE sss_fie_evaluators");
Database::query("DROP TABLE sss_caseload_transfers");
Database::query("DROP TABLE sss_caseload");

if (Database::sequenceExists('sss_caseload_id_seq')) {
	Database::dropSequence("sss_caseload_id_seq");
}
Database::createSequence("sss_caseload_id_seq");
Database::query(Database::preprocess("
	CREATE TABLE sss_caseload(
		id BIGINT PRIMARY KEY DEFAULT {{next:sss_caseload_id_seq}},
		created_at {$timestamp},
		student_id numeric REFERENCES students(student_id) NOT NULL
	)
"));
