<?php

$directory = SSRSBatch::getDirectory();

if(!file_exists($directory)) {
	mkdir($directory);
}

if(!Database::sequenceExists('ssrs_batch_seq')) {
	Database::createSequence('ssrs_batch_seq');
}

if(!Database::sequenceExists('ssrs_batch_record_seq')) {
	Database::createSequence('ssrs_batch_record_seq');
}

if(!Database::sequenceExists('ssrs_publish_seq')) {
	Database::createSequence('ssrs_publish_seq');
}

if(!Database::sequenceExists('ssrs_publish_school_seq')) {
	Database::createSequence('ssrs_publish_school_seq');
}

if(!Database::sequenceExists('ssrs_publish_grade_level_seq')) {
	Database::createSequence('ssrs_publish_grade_level_seq');
}

if(Database::$type === 'postgres') {
	$batch_id               = "nextval('ssrs_batch_seq')";
	$record_id              = "nextval('ssrs_batch_record_seq')";
	$publish_id             = "nextval('ssrs_publish_seq')";
	$publish_school_id      = "nextval('ssrs_publish_school_seq')";
	$publish_grade_level_id = "nextval('ssrs_publish_grade_level_seq')";
	$text                   = 'TEXT';
	$timestamp              = 'TIMESTAMP';
}
else {
	$batch_id               = "NEXT VALUE FOR ssrs_batch_seq";
	$record_id              = "NEXT VALUE FOR ssrs_batch_record_seq";
	$publish_id             = "NEXT VALUE FOR ssrs_publish_seq";
	$publish_school_id      = "NEXT VALUE FOR ssrs_publish_school_seq";
	$publish_grade_level_id = "NEXT VALUE FOR ssrs_publish_grade_level_seq";
	$text                   = 'VARCHAR(max)';
	$timestamp              = 'DATETIME2';
}

if(!Database::tableExists('ssrs_batch')) {
	Database::query("
		CREATE TABLE ssrs_batch (
			id BIGINT PRIMARY KEY DEFAULT {$batch_id},
			name VARCHAR(255),
			ssrs_template VARCHAR(255),
			school_id BIGINT,
			syear BIGINT,
			marking_period VARCHAR(255),
			sort VARCHAR(255),
			started BIGINT,
			completed BIGINT,
			error {$text},
			hash VARCHAR(255),
			multilingual BIGINT,
			created_at {$timestamp},
			created_by_class VARCHAR(255),
			created_by_id BIGINT
		)
	");
}
else {
	if(Database::columnExists('ssrs_batch', 'grade_level')) {
		Database::dropColumn('ssrs_batch', 'grade_level');
	}

	if(!Database::columnExists('ssrs_batch', 'name')) {
		Database::createColumn('ssrs_batch', 'name', 'VARCHAR', '255');
	}

	if(!Database::columnExists('ssrs_batch', 'hash')) {
		Database::createColumn('ssrs_batch', 'hash', 'VARCHAR', '255');

		$batches = SSRSBatch::getAllAndLoad();

		foreach($batches as $batch_id => $batch) {
			$path = "{$directory}/{$batch_id}.pdf";
			$hash = sha1_file($path);

			$batch
				->setHash($hash)
				->persist();
		}
	}

	if(!Database::columnExists('ssrs_batch', 'multilingual')) {
		Database::createColumn('ssrs_batch', 'multilingual', 'BIGINT');
	}
}

// For RISD only
$risd = !empty($GLOBALS['ClientId']) && intval($GLOBALS['ClientId']) === 20862;

if($risd && !Database::columnExists('ssrs_batch', 'print_mailer')) {
	Database::createColumn('ssrs_batch', 'print_mailer', 'BIGINT');
}
//

if(!Database::tableExists('ssrs_batch_record')) {
	Database::query("
		CREATE TABLE ssrs_batch_record (
			id BIGINT PRIMARY KEY DEFAULT {$record_id},
			ssrs_batch_id BIGINT,
			source_class VARCHAR(255),
			source_id BIGINT
		)
	");
}

if(!Database::tableExists('ssrs_publish')) {
	Database::query("
		CREATE TABLE ssrs_publish (
			id BIGINT PRIMARY KEY DEFAULT {$publish_id},
			syear BIGINT,
			marking_period VARCHAR(255),
			ssrs_template VARCHAR(255),
			start_date DATE,
			start_time TIME,
			end_date DATE,
			end_time TIME,
			multilingual BIGINT
		)
	");
}
else {
	if(!Database::columnExists('ssrs_publish', 'start_time')) {
		Database::createColumn('ssrs_publish', 'start_time', 'TIME');
	}

	if(!Database::columnExists('ssrs_publish', 'end_time')) {
		Database::createColumn('ssrs_publish', 'end_time', 'TIME');
	}

	if(!Database::columnExists('ssrs_publish', 'multilingual')) {
		Database::createColumn('ssrs_publish', 'multilingual', 'BIGINT');
	}
}

if(!Database::tableExists('ssrs_publish_school')) {
	Database::query("
		CREATE TABLE ssrs_publish_school (
			id BIGINT PRIMARY KEY DEFAULT {$publish_school_id},
			ssrs_publish_id BIGINT,
			school_id BIGINT
		)
	");
}

if(!Database::tableExists('ssrs_publish_grade_level')) {
	Database::query("
		CREATE TABLE ssrs_publish_grade_level (
			id BIGINT PRIMARY KEY DEFAULT {$publish_grade_level_id},
			ssrs_publish_id BIGINT,
			grade_level VARCHAR(255)
		)
	");
}

if(!Database::columnExists('schools', 'min_syear')) {
	Database::createColumn('schools', 'min_syear', 'INT');
}

if(!Database::columnExists('schools', 'max_syear')) {
	Database::createColumn('schools', 'max_syear', 'INT');
}
