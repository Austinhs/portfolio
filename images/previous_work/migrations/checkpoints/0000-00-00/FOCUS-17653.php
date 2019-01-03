<?php

// Tags: SSS

if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return;
}

if (!Database::columnExists('sss_domains', 'event_instance_id')) {
	Database::createColumn('sss_domains', 'event_instance_id', 'bigint');
}

if (!Database::columnExists('sss_domains', 'form_instance_id')) {
	Database::createColumn('sss_domains', 'form_instance_id', 'bigint');
}

if (!Database::columnExists('sss_goals', 'modified_course')) {
	Database::createColumn('sss_goals', 'modified_course', 'text');
}

if (!Database::columnExists('sss_goals', 'modified_grade')) {
	Database::createColumn('sss_goals', 'modified_grade', 'text');
}

if (!Database::columnExists('sss_goals', 'modified_level')) {
	Database::createColumn('sss_goals', 'modified_level', 'text');
}

if (!Database::columnExists('sss_goals', 'modified_alternate')) {
	Database::createColumn('sss_goals', 'modified_alternate', 'text');
}

Database::changeColumnType('sss_goals', 'condition', 'varchar', 255, true);
