<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (Database::$type === 'postgres') {
	$boolean   = 'boolean';
} else {
	$boolean   = 'bit';
}

if (!Database::columnExists('focus_tables', 'last_signature')) {
	Database::createColumn('focus_tables', 'last_signature', 'varchar', 40);
}

if (!Database::columnExists('focus_table_records', 'last_signature')) {
	Database::createColumn('focus_table_records', 'last_signature', 'varchar', 40);
}

if (!Database::columnExists('sss_progress_codes', 'enabled')) {
	Database::createColumn('sss_progress_codes', 'enabled', $boolean);
}

$constraints = Database::getConstraints('sss_forms');
if (!isset($constraints['sss_forms_new_form_id_foreign'])) {
	Database::query("ALTER TABLE sss_forms ADD CONSTRAINT sss_forms_new_form_id_foreign FOREIGN KEY (new_form_id) REFERENCES formbuilder_forms(id)");
}
