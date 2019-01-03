<?php

echo "8.0.1 Migration";

// For general use
$text_type = Database::$type === 'postgres' ? 'TEXT' : 'VARCHAR(MAX)';

// This was in 8.2.3.sql, but needs to be added ASAP
if(!Database::columnExists('user_audit_trail', 'logged_in_as')) {
	Database::createColumn('user_audit_trail', 'logged_in_as', 'bigint');
}

if(!Database::columnExists('database_object_log', 'logged_in_class')) {
	Database::createColumn('database_object_log', 'logged_in_class', 'varchar');
}

if(!Database::columnExists('database_object_log', 'logged_in_id')) {
	Database::createColumn('database_object_log', 'logged_in_id', 'bigint');
}

// Change address.number to NUMERIC
Database::changeColumnType('address', 'number', 'numeric');

// Add a focus_files_expiration source for CustomFields files
$source     = 'CustomFields_%';
$expiration = 0;

$sql = "
	SELECT
		*
	FROM
		focus_files_expiration
	WHERE
		source = :source
";

$params = [
	'source' => $source
];

$rows = Database::get($sql, $params);

if(empty($rows)) {
	$sql = "
		INSERT INTO focus_files_expiration (
			source,
			expiration
		)
		VALUES (
			:source,
			:expiration
		)
	";

	$params = [
		'source'     => $source,
		'expiration' => $expiration
	];

	Database::query($sql, $params);
}

// Create the "automated_cron_emails" table and sequence
if(!Database::sequenceExists('automated_cron_emails_seq')) {
	Database::createSequence('automated_cron_emails_seq');
}

if(!Database::tableExists('automated_cron_emails')) {
	$sql = Database::preprocess("
		CREATE TABLE automated_cron_emails(
			id NUMERIC PRIMARY KEY DEFAULT {{next:automated_cron_emails_seq}},
			title VARCHAR(255),
			query {$text_type} NULL,
			message {$text_type} NULL,
			message_is_query NUMERIC DEFAULT 0,
			email_on_empty NUMERIC DEFAULT 0,
			enabled NUMERIC DEFAULT 1,
			hour SMALLINT DEFAULT 0,
			min SMALLINT DEFAULT 0,
			sunday VARCHAR(1) DEFAULT 'Y',
			monday VARCHAR(1) DEFAULT 'Y',
			tuesday VARCHAR(1) DEFAULT 'Y',
			wednesday VARCHAR(1) DEFAULT 'Y',
			thursday VARCHAR(1) DEFAULT 'Y',
			friday VARCHAR(1) DEFAULT 'Y',
			saturday VARCHAR(1) DEFAULT 'Y'
		)
	");

	Database::query($sql);
}

// Create the "automated_cron_email_users" table and sequence
if(!Database::sequenceExists('automated_cron_email_users_seq')) {
	Database::createSequence('automated_cron_email_users_seq');
}

if(!Database::tableExists('automated_cron_email_users')) {
	$sql = Database::preprocess("
		CREATE TABLE automated_cron_email_users(
			id NUMERIC PRIMARY KEY DEFAULT {{next:automated_cron_email_users_seq}},
			automated_cron_email_id NUMERIC NOT NULL,
			staff_id NUMERIC NOT NULL
		)
	");

	Database::query($sql);
}

// Create the "automated_cron_email_profiles" table and sequence
if(!Database::sequenceExists('automated_cron_email_profiles_seq')) {
	Database::createSequence('automated_cron_email_profiles_seq');
}

if(!Database::tableExists('automated_cron_email_profiles')) {
	$sql = Database::preprocess("
		CREATE TABLE automated_cron_email_profiles(
			id NUMERIC PRIMARY KEY DEFAULT {{next:automated_cron_email_profiles_seq}},
			automated_cron_email_id NUMERIC NOT NULL,
			profile_id NUMERIC NOT NULL
		)
	");

	Database::query($sql);
}

// Delete existing cron entries for this job
Database::query("
	DELETE FROM
		cron_jobs
	WHERE
		class = 'DailyAutomatedEmailJob'
");

// Create a cron entry for this job
$sql = Database::preprocess("
	INSERT INTO cron_jobs(
		{{postgres:id,}}
		hour,
		minute,
		sunday,
		monday,
		tuesday,
		wednesday,
		thursday,
		friday,
		saturday,
		class,
		title,
		priority
	)
	VALUES (
		{{postgres:{{next:cron_jobs_seq}},}}
		0,
		0,
		'Y',
		'Y',
		'Y',
		'Y',
		'Y',
		'Y',
		'Y',
		'DailyAutomatedEmailJob',
		'Automated Emails',
		0
	)
");

Database::query($sql);

// Update "user_permission.user_id" to substring off the silly 'U' at the beginning
$indexes = Database::getIndexes('user_permission');

if(!empty($indexes['user_permission_ind1'])) {
	$on_str = Database::$type === 'mssql' ? " ON user_permission" : '';

	Database::query("
		DROP INDEX user_permission_ind1{$on_str}
	");
}

$type = strtolower(Database::getColumnType('user_permission', 'user_id'));

if(strpos($type, 'int') === false) {
	Database::query("
		UPDATE
			user_permission
		SET
			user_id = SUBSTRING(user_id, 2, 100)
		WHERE
			CAST(user_id AS VARCHAR(255)) LIKE 'U%'
	");

	Database::changeColumnType('user_permission', 'user_id', 'bigint', '', false);
}

// Recreate the "user_permission_ind1" index
$indexes = Database::getIndexes('user_permission');

if(empty($indexes['user_permission_ind1'])) {
	Database::query("
		CREATE UNIQUE INDEX
			user_permission_ind1
		ON
			user_permission (\"user_id\", \"key\")
	");
}
