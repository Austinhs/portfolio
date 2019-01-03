<?php

$tables=[
	"STUDENTS",
	"STUDENT_ENROLLMENT",
	"USERS",
	"COURSES",
	"COURSE_PERIODS",
	"SCHEDULE",
	"STUDENT_REPORT_CARD_GRADES",
	"ADDRESS",
	"STUDENTS_JOIN_ADDRESS",
	"PEOPLE",
	"STUDENTS_JOIN_PEOPLE",
	"PEOPLE_JOIN_CONTACTS",
	"CUSTOM_FIELD_LOG_ENTRIES",
	"ATTENDANCE_PERIOD"
];

$postgresSyntaxPrimaryKey='not null identity';
$postgresSyntaxNullable='NULL';

if(Database::$type=='postgres') {
	$postgresSyntaxPrimaryKey = '';
	$postgresSyntaxNullable = '';

	$sequences = [
		"csv_import_templates_seq",
		"doe_import_templates_seq",
		"import_tool_log_seq"
	];
	foreach ($sequences as $sequence) {
		if (!Database::sequenceExists($sequence)) {
			Database::createSequence($sequence,1,1);
		}
	}
}

foreach ($tables as $table) {
	if (!Database::columnExists($table, 'imported')) {
		Database::createColumn($table, 'IMPORTED','char',1);
	}
}

if (!Database::tableExists('CSV_IMPORT_TEMPLATES')){
	Database::query("
		CREATE TABLE CSV_IMPORT_TEMPLATES(
		ID numeric {$postgresSyntaxPrimaryKey} primary key,
		TITLE varchar(100),
		MODNAME varchar(250),
		ITEM_ID numeric,
		COLUMN_NUMBER numeric,
		MAPPED_FIELDS varchar(100)
		)");
}

if (!Database::tableExists('DOE_IMPORT_TEMPLATES')){
	Database::query("
		CREATE TABLE DOE_IMPORT_TEMPLATES(
		ID numeric {$postgresSyntaxPrimaryKey} primary key,
		TITLE varchar(100),
		MODNAME varchar(250),
		ITEM_ID numeric,
		START_POINT numeric,
		END_POINT numeric,
		CHARNUM numeric,
		MAPPED_FIELDS varchar(100)
		)");
}

if (!Database::tableExists('IMPORT_TOOL_LOG')){
	Database::query("
		CREATE TABLE IMPORT_TOOL_LOG (
		RUN_ID BIGINT {$postgresSyntaxPrimaryKey} PRIMARY KEY,
		PID INTEGER {$postgresSyntaxNullable},
		TOOL_NAME VARCHAR(64) {$postgresSyntaxNullable},
		START_DATE DATE {$postgresSyntaxNullable},
		END_DATE DATE {$postgresSyntaxNullable},
		STATUS VARCHAR(32),
		\"CHECKPOINT\" VARCHAR(64) {$postgresSyntaxNullable},
		\"ROWS\" BIGINT NOT NULL DEFAULT 0,
		EXECUTED INTEGER NOT NULL DEFAULT 0,
		INSERTED INTEGER NOT NULL DEFAULT 0,
		UPDATED INTEGER NOT NULL DEFAULT 0,
		DELETED INTEGER NOT NULL DEFAULT 0,
		INVALID INTEGER NOT NULL DEFAULT 0,
		IGNORED INTEGER NOT NULL DEFAULT 0
		)");
}

$indexedFields=[
	"MODNAME",
	"MAPPED_FIELDS",
	"ITEM_ID"
];
foreach ($indexedFields as $indexedField) {
	$indexName="CSV_IMPORT_TEMPLATES_{$indexedField}_INDEX";
	if (!Database::indexExists('CSV_IMPORT_TEMPLATES',$indexName)){
		Database::query("CREATE INDEX {$indexName} ON CSV_IMPORT_TEMPLATES({$indexedField})");
	}

	$indexName="DOE_IMPORT_TEMPLATES_{$indexedField}_INDEX";
	if (!Database::indexExists('DOE_IMPORT_TEMPLATES',$indexName)){
		Database::query("CREATE INDEX {$indexName} ON DOE_IMPORT_TEMPLATES({$indexedField})");
	}
}
