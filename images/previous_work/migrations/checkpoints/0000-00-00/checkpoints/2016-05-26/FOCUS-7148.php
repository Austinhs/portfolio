<?php

$inserts = ["INSERT INTO importer_templates (name, type, settings) VALUES ('Students', 'main', '{\"destinationTable\":\"students\",\"temporaryTable\":\"StudentsTempImporter\",\"errorTable\":\"StudentsTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (name, type, settings) VALUES ('Users', 'main', '{\"destinationTable\":\"users\",\"temporaryTable\":\"UsersTempImporter\",\"errorTable\":\"UsersTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (name, type, settings) VALUES ('User Log Entries', 'main', '{\"destinationTable\":\"custom_field_log_entries_users\",\"temporaryTable\":\"UserLogsTempImporter\",\"errorTable\":\"UserLogsTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (name, type, settings) VALUES ('Student Log Entries', 'main', '{\"destinationTable\":\"custom_field_log_entries_students\",\"temporaryTable\":\"StudentLoggingFieldsTempImporter\",\"errorTable\":\"StudentLoggingFieldsTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (name, type, settings) VALUES ('Course Subjects', 'main', '{\"destinationTable\":\"course_subjects\",\"temporaryTable\":\"CourseSubjectsTempImporter\",\"errorTable\":\"CourseSubjectsTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (name, type, settings) VALUES ('Course Periods', 'main', '{\"destinationTable\":\"course_periods\",\"temporaryTable\":\"coursePeriodsTempImporter\",\"errorTable\":\"coursePeriodsTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (name, type, settings) VALUES ('Courses', 'main', '{\"destinationTable\":\"courses\",\"temporaryTable\":\"coursesTempImporter\",\"errorTable\":\"coursesTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (name, type, settings) VALUES ('Student Enrollment', 'main', '{\"destinationTable\":\"student_enrollment\",\"temporaryTable\":\"studentEnrollTempImporter\",\"errorTable\":\"studentEnrollTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (name, type, settings) VALUES ('Schedule', 'main', '{\"destinationTable\":\"schedule\",\"temporaryTable\":\"scheduleTempImporter\",\"errorTable\":\"scheduleTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (NAME, TYPE, SETTINGS) VALUES('Resources', 'main', '{\"destinationTable\":\"resources\",\"temporaryTable\":\"resourcesTempImporter\",\"errorTable\":\"resourcesTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (NAME, TYPE, SETTINGS) VALUES('Course Catalog', 'main', '{\"destinationTable\":\"master_courses\",\"temporaryTable\":\"masterCoursesTempImporter\",\"errorTable\":\"masterCoursesTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_templates (NAME, TYPE, SETTINGS) VALUES('User Enrollment', 'main', '{\"destinationTable\":\"user_enrollment\",\"temporaryTable\":\"userEnrollmentTempImporter\",\"errorTable\":\"userEnrollmentTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('custom_field_log_entries_students', 'id', '[\"source_id\",\"field_id\"]', '[]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('custom_field_log_entries_users', 'id', '[\"source_id\",\"field_id\"]', '[]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('students', 'student_id', '[\"Demographic ENR > Pupil Number (custom_53)\"]', '[\"first_name\",\"last_name\"]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('users', 'staff_id', '[\"Confidential > Social Security Number (custom_556)\"]', '[\"first_name\",\"last_name\"]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('course_subjects', 'subject_id', '[\"syear\",\"school_id\",\"short_name\"]', '[\"title\"]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('schools', 'id', '[\"custom_327\"]', '[\"title\"]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('course_periods', 'course_period_id', '[\"school_id\",\"course_id\",\"short_name\"]', '[\"syear\",\"course_weight\",\"grade_posting_scheme_id\"]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('courses', 'course_id', '[\"school_id\",\"title\",\"short_name\",\"syear\"]', '[]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('student_enrollment', 'id', '[\"student_id\",\"school_id\",\"syear\"]', '[\"start_date\",\"include_in_class_rank\"]');",
    "INSERT INTO importer_keys (table_name, identity_column, primary_keys, required_fields) VALUES ('schedule', 'id', '[\"school_id\",\"student_id\",\"course_id\",\"course_period_id\",\"course_weight\",\"marking_period_id\"]', '[]');",
    "INSERT INTO importer_keys (TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS) values('resources', 'id', '[\"school_id\",\"short_name\"]', '[\"category_id\"]');",
    "INSERT INTO importer_keys (TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS) values('master_courses', 'course_id', '[\"syear\",\"short_name\"]', '[\"status\",\"title\"]');",
    "INSERT INTO importer_keys (TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS) values('user_enrollment', 'id', '[\"staff_id\",\"start_date\",\"end_date\"]', '[\"profiles\"]');"];


if(Database::$type === 'mssql'){

	if(!Database::tableExists('importer_keys'))
	{
		Database::query(
			"
				CREATE TABLE importer_keys
				(
					id bigint IDENTITY(1,1) PRIMARY KEY,
					table_name VARCHAR(60) NOT NULL,
					identity_column VARCHAR(max) NOT NULL,
					primary_keys VARCHAR(max),
					required_fields VARCHAR(max),
					post_sql VARCHAR(max),
					defaults VARCHAR(max)
				);
			"
		);
	}

	if(!Database::tableExists('importer_logs'))
	{
		Database::query(
			"
				CREATE TABLE importer_logs
				(
					date TIMESTAMP,
					deleted INT,
					destination_table VARCHAR(255),
					execution DOUBLE PRECISION,
					filename VARCHAR,
					format VARCHAR(255),
					id bigint IDENTITY(1,1) PRIMARY KEY,
					inserted INT,
					invalid INT,
					method VARCHAR(255),
					temporary_table VARCHAR(255),
					total INT,
					updated INT,
					valid INT
				);
			"
		);
	}

	if(!Database::tableExists('importer_templates'))
	{
		Database::query(
			"
				CREATE TABLE importer_templates
				(
					id bigint IDENTITY(1,1) PRIMARY KEY,
					name VARCHAR(255),
					type VARCHAR(255),
					settings VARCHAR(max)
				);
			"
		);
	}
}

elseif(Database::$type === 'postgres'){

	if(!Database::tableExists('importer_keys'))
	{
		Database::query(
			"
				CREATE TABLE importer_keys
				(
					id SERIAL PRIMARY KEY NOT NULL,
					table_name VARCHAR(60) NOT NULL,
					identity_column VARCHAR NOT NULL,
					primary_keys VARCHAR,
					required_fields VARCHAR,
					post_sql VARCHAR,
					defaults VARCHAR
				);
			"
		);
	}

	if(!Database::tableExists('importer_logs'))
	{
		Database::query(
			"
				CREATE TABLE importer_logs
				(
					date TIMESTAMP,
					deleted INT,
					destination_table VARCHAR(255),
					execution DOUBLE PRECISION,
					filename VARCHAR,
					format VARCHAR(255),
					id SERIAL PRIMARY KEY NOT NULL,
					inserted INT,
					invalid INT,
					method VARCHAR(255),
					temporary_table VARCHAR(255),
					total INT,
					updated INT,
					valid INT
				);
			"
		);
	}


	if(!Database::tableExists('importer_templates'))
	{
		Database::query(
			"
				CREATE TABLE importer_templates
				(
					id SERIAL PRIMARY KEY NOT NULL,
					name VARCHAR(255),
					type VARCHAR(255),
					settings VARCHAR
				);
			"
		);
	}
}

foreach ($inserts as $insert) {
    echo $insert . "\n";
    Database::query($insert);
}