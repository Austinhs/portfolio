<?php

$warehouse = __DIR__ . '/../Warehouse.php';

if(file_exists($warehouse)) {
	require_once($warehouse);
}

if(!Database::tableExists('web_page')) {
	Database::query("CREATE TABLE web_page (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('web_page_seq')) {
	Database::createSequence('web_page_seq');
}

if(!Database::columnExists('web_page', 'type')) {
	Database::createColumn('web_page', 'type', 'VARCHAR(255)', null, false);
}

if(!Database::columnExists('web_page', 'username')) {
	Database::createColumn('web_page', 'username', 'VARCHAR(255)');
}

if(!Database::columnExists('web_page', 'title')) {
	Database::createColumn('web_page', 'title', 'VARCHAR(255)');
}

if(!Database::columnExists('web_page', 'system_image')) {
	Database::createColumn('web_page', 'system_image', 'VARCHAR(255)');
}

if(!Database::columnExists('web_page', 'image')) {
	Database::createColumn('web_page', 'image', 'BIGINT');
}

if(!Database::columnExists('web_page', 'year')) {
	Database::createColumn('web_page', 'year', 'BIGINT');
}

if(!Database::columnExists('web_page', 'is_public')) {
	Database::createColumn('web_page', 'is_public', 'BIGINT');
}

if(!Database::columnExists('web_page', 'is_disabled')) {
	Database::createColumn('web_page', 'is_disabled', 'BIGINT');
}

if(!Database::columnExists('web_page', 'created_at')) {
	Database::createColumn('web_page', 'created_at', 'TIMESTAMP');
}

if(!Database::columnExists('web_page', 'updated_at')) {
	Database::createColumn('web_page', 'updated_at', 'TIMESTAMP');
}

if(!Database::columnExists('web_page', 'deleted_at')) {
	Database::createColumn('web_page', 'deleted_at', 'TIMESTAMP');
}

// Page Profiles

if(!Database::tableExists('web_page_profile')) {
	Database::query("CREATE TABLE web_page_profile (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('web_page_profile_seq')) {
	Database::createSequence('web_page_profile_seq');
}

if(!Database::columnExists('web_page_profile', 'web_page_id')) {
	Database::createColumn('web_page_profile', 'web_page_id', 'BIGINT');
}

if(!Database::columnExists('web_page_profile', 'profile_id')) {
	Database::createColumn('web_page_profile', 'profile_id', 'BIGINT');
}

// Page Sections

if(!Database::tableExists('web_page_section')) {
	Database::query("CREATE TABLE web_page_section (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('web_page_section_seq')) {
	Database::createSequence('web_page_section_seq');
}

if(!Database::columnExists('web_page_section', 'web_page_id')) {
	Database::createColumn('web_page_section', 'web_page_id', 'BIGINT');
}

if(!Database::columnExists('web_page_section', 'section_id')) {
	Database::createColumn('web_page_section', 'section_id', 'BIGINT');
}

// Posts

if(!Database::tableExists('web_page_post')) {
	Database::query("CREATE TABLE web_page_post (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('web_page_post_seq')) {
	Database::createSequence('web_page_post_seq');
}

if(!Database::columnExists('web_page_post', 'web_page_id')) {
	Database::createColumn('web_page_post', 'web_page_id', 'BIGINT');
}

if(!Database::columnExists('web_page_post', 'body')) {
	Database::createColumn('web_page_post', 'body', 'TEXT');
}

if(!Database::columnExists('web_page_post', 'send_push_notifications')) {
	Database::createColumn('web_page_post', 'send_push_notifications', 'BIGINT');
}

if(!Database::columnExists('web_page_post', 'is_public')) {
	Database::createColumn('web_page_post', 'is_public', 'BIGINT');
}

if(!Database::columnExists('web_page_post', 'is_disabled')) {
	Database::createColumn('web_page_post', 'is_disabled', 'BIGINT');
}

if(!Database::columnExists('web_page_post', 'created_at')) {
	Database::createColumn('web_page_post', 'created_at', 'TIMESTAMP');
}

if(!Database::columnExists('web_page_post', 'updated_at')) {
	Database::createColumn('web_page_post', 'updated_at', 'TIMESTAMP');
}

if(!Database::columnExists('web_page_post', 'deleted_at')) {
	Database::createColumn('web_page_post', 'deleted_at', 'TIMESTAMP');
}

// Post Profiles

if(!Database::tableExists('web_page_post_profile')) {
	Database::query("CREATE TABLE web_page_post_profile (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('web_page_post_profile_seq')) {
	Database::createSequence('web_page_post_profile_seq');
}

if(!Database::columnExists('web_page_post_profile', 'web_page_post_id')) {
	Database::createColumn('web_page_post_profile', 'web_page_post_id', 'BIGINT');
}

if(!Database::columnExists('web_page_post_profile', 'profile_id')) {
	Database::createColumn('web_page_post_profile', 'profile_id', 'BIGINT');
}

// Resources

if(!Database::tableExists('web_page_resource')) {
	Database::query("CREATE TABLE web_page_resource (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('web_page_resource_seq')) {
	Database::createSequence('web_page_resource_seq');
}

if(!Database::columnExists('web_page_resource', 'web_page_id')) {
	Database::createColumn('web_page_resource', 'web_page_id', 'BIGINT');
}

if(!Database::columnExists('web_page_resource', 'parent_id')) {
	Database::createColumn('web_page_resource', 'parent_id', 'BIGINT');
}

if(!Database::columnExists('web_page_resource', 'type')) {
	Database::createColumn('web_page_resource', 'type', 'VARCHAR(255)');
}

if(!Database::columnExists('web_page_resource', 'title')) {
	Database::createColumn('web_page_resource', 'title', 'VARCHAR(255)');
}

if(!Database::columnExists('web_page_resource', 'system_image')) {
	Database::createColumn('web_page_resource', 'system_image', 'VARCHAR(255)');
}

if(!Database::columnExists('web_page_resource', 'image')) {
	Database::createColumn('web_page_resource', 'image', 'BIGINT');
}

if(!Database::columnExists('web_page_resource', 'href')) {
	Database::createColumn('web_page_resource', 'href', 'TEXT');
}

if(!Database::columnExists('web_page_resource', 'sort')) {
	Database::createColumn('web_page_resource', 'sort', 'BIGINT');
}

if(!Database::columnExists('web_page_resource', 'is_public')) {
	Database::createColumn('web_page_resource', 'is_public', 'BIGINT');
}

if(!Database::columnExists('web_page_resource', 'is_disabled')) {
	Database::createColumn('web_page_resource', 'is_disabled', 'BIGINT');
}

if(!Database::columnExists('web_page_resource', 'created_at')) {
	Database::createColumn('web_page_resource', 'created_at', 'TIMESTAMP');
}

if(!Database::columnExists('web_page_resource', 'updated_at')) {
	Database::createColumn('web_page_resource', 'updated_at', 'TIMESTAMP');
}

if(!Database::columnExists('web_page_resource', 'deleted_at')) {
	Database::createColumn('web_page_resource', 'deleted_at', 'TIMESTAMP');
}

// Resource Profiles

if(!Database::tableExists('web_page_resource_profile')) {
	Database::query("CREATE TABLE web_page_resource_profile (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('web_page_resource_profile_seq')) {
	Database::createSequence('web_page_resource_profile_seq');
}

if(!Database::columnExists('web_page_resource_profile', 'web_page_resource_id')) {
	Database::createColumn('web_page_resource_profile', 'web_page_resource_id', 'BIGINT');
}

if(!Database::columnExists('web_page_resource_profile', 'profile_id')) {
	Database::createColumn('web_page_resource_profile', 'profile_id', 'BIGINT');
}

// Course Periods

if(!Database::columnExists('course_periods', 'web_page_id')) {
	Database::createColumn('course_periods', 'web_page_id', 'BIGINT');
}
