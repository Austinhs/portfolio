<?php

$text_type = Database::$type === 'mssql' ? 'VARCHAR(MAX)' : 'TEXT';

// This should probably be changed to DATETIME2 for mssql, but for now I am just fixing the issue I know about - Bob M.
$date_type = Database::$type === 'mssql' ? 'DATETIME' : 'TIMESTAMP';

if(!Database::sequenceExists('login_token_seq')) {
	Database::createSequence("login_token_seq");
}

if(!Database::tableExists('login_token')) {
	Database::query("
		CREATE TABLE login_token (
			id BIGINT PRIMARY KEY,
			type VARCHAR(255),
			username VARCHAR(255),
			token VARCHAR(255),
			expiration {$date_type},
			uses BIGINT
		)
	");
}

if(!Database::sequenceExists('community_app_device_seq')) {
	Database::createSequence("community_app_device_seq");
}

if(!Database::tableExists('community_app_device')) {
	Database::query("
		CREATE TABLE community_app_device (
			id BIGINT PRIMARY KEY,
			uuid VARCHAR(255),
			type VARCHAR(255),
			notification_token {$text_type},
			username VARCHAR(255)
		)
	");
}

if(!Database::sequenceExists('community_app_link_seq')) {
	Database::createSequence("community_app_link_seq");
}

if(!Database::tableExists('community_app_link')) {
	Database::query("
		CREATE TABLE community_app_link (
			id BIGINT PRIMARY KEY,
			parent_id BIGINT,
			title VARCHAR(255),
			url VARCHAR(255),
			icon VARCHAR(255),
			type VARCHAR(255),
			sort BIGINT
		)
	");
}

if(!Database::sequenceExists('community_app_link_profile_seq')) {
	Database::createSequence("community_app_link_profile_seq");
}

if(!Database::tableExists('community_app_link_profile')) {
	Database::query("
		CREATE TABLE community_app_link_profile (
			id BIGINT PRIMARY KEY,
			link_id BIGINT,
			profile_id BIGINT
		)
	");
}

if(!Database::sequenceExists('community_app_feed_seq')) {
	Database::createSequence("community_app_feed_seq");
}

if(!Database::tableExists('community_app_feed')) {
	Database::query("
		CREATE TABLE community_app_feed (
			id BIGINT PRIMARY KEY,
			title VARCHAR(255),
			school_id BIGINT,
			default_feed BIGINT,
			facebook VARCHAR(255),
			twitter VARCHAR(255),
			rss VARCHAR(255)
		)
	");
}

if(!Database::sequenceExists('community_app_feed_subscription_seq')) {
	Database::createSequence("community_app_feed_subscription_seq");
}

if(!Database::tableExists('community_app_feed_subscription')) {
	Database::query("
		CREATE TABLE community_app_feed_subscription (
			id BIGINT PRIMARY KEY,
			uuid VARCHAR(255),
			feed_id BIGINT
		)
	");
}

if(!Database::sequenceExists('community_app_school_subscription_seq')) {
	Database::createSequence("community_app_school_subscription_seq");
}

if(!Database::tableExists('community_app_school_subscription')) {
	Database::query("
		CREATE TABLE community_app_school_subscription (
			id BIGINT PRIMARY KEY,
			uuid VARCHAR(255),
			school_id BIGINT
		)
	");
}

if(!Database::sequenceExists('community_app_notification_seq')) {
	Database::createSequence("community_app_notification_seq");
}

if(!Database::tableExists('community_app_notification')) {
	Database::query(Database::preprocess("
		CREATE TABLE community_app_notification (
			id BIGINT PRIMARY KEY,
			title VARCHAR(255),
			body {$text_type},
			data {$text_type},
			created_at {$date_type},
			scheduled_at {$date_type},
			sent_at {$date_type}
		)
	"));
}

if(!Database::sequenceExists('community_app_notification_device_seq')) {
	Database::createSequence("community_app_notification_device_seq");
}

if(!Database::tableExists('community_app_notification_device')) {
	Database::query(Database::preprocess("
		CREATE TABLE community_app_notification_device (
			id BIGINT PRIMARY KEY,
			device_id BIGINT,
			notification_id BIGINT
		)
	"));
}

Database::createColumn('portal_notes', 'notification_id', 'BIGINT');
Database::changeColumnType('portal_notes', 'published_date', 'TIMESTAMP');
