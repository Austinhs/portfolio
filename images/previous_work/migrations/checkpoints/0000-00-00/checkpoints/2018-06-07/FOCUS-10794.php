<?php
// Tags: SSS, Formbuilder
Migrations::depend('FOCUS-9271');
Migrations::depend('FOCUS-9271b');

if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (Database::$type === 'postgres') {
	$text      = 'text';
	$timestamp = 'timestamp without time zone';
	$limit     = 'LIMIT 1';
} else {
	$text      = 'varchar(max)';
	$timestamp = 'datetime2(6)';
	$top       = 'TOP 1';
}

if (!Database::columnExists('sss_logging_field_options', 'order_by')) {
	Database::createColumn('sss_logging_field_options', 'order_by', 'integer');
}

if (!Database::columnExists('sss_permissions', 'program_id')) {
	Database::createColumn('sss_permissions', 'program_id', 'bigint');
}

if (!Database::columnExists('sss_events', 'program_id')) {
	Database::createColumn('sss_events', 'program_id', 'bigint');
}

if (!Database::columnExists('sss_event_instances', 'program_id')) {
	Database::createColumn('sss_event_instances', 'program_id', 'bigint');
}

if (!Database::columnExists('sss_logging_field_options', 'options')) {
	Database::createColumn('sss_logging_field_options', 'options', $text);
}

// If migration 8295 ran with sss disabled, this is not needed
if (!Database::tableExists('formbuilder_collections')) {
	if (!Database::tableExists('sss_form_collections')) {
		Database::createSequence('sss_form_collections_id_seq');
		Database::query(Database::preprocess("
			CREATE TABLE sss_form_collections (
				id BIGINT PRIMARY KEY DEFAULT {{next:sss_form_collections_id_seq}} NOT NULL,
				form_id BIGINT REFERENCES sss_forms(id) ON DELETE CASCADE,
				name VARCHAR(255),
				sql {$text},
				deleted_at {$timestamp}
			)
		"));
	} else if (Database::$type === 'mssql') {
		$identity = Database::get("SELECT 1 FROM sys.identity_columns WHERE OBJECT_NAME(object_id) = 'sss_form_collections'");
		if (!empty($identity)) {
			Database::createSequence('sss_form_collections_id_seq');
			Database::query("ALTER TABLE sss_form_collections ADD tmp_id BIGINT DEFAULT NEXT VALUE FOR sss_form_collections_id_seq");
			Database::query("UPDATE sss_form_collections SET tmp_id = id");
			$constraints = Database::getConstraints("sss_form_collections");
			foreach ($constraints as $constraint_name => $value) {
				Database::query("ALTER TABLE sss_form_collections DROP CONSTRAINT {$constraint_name}");
			}
			Database::query("ALTER TABLE sss_form_collections drop column id");
			Database::renameColumn('tmp_id', 'id', 'sss_form_collections');
		}
	}
}

// If Laravel migrations have never been ran, table will not exist on site
if (Database::tableExists('migrations')) {
	$ran_migration = Database::get("SELECT 1 FROM migrations WHERE migration = '2017_01_24_165145_change_program_category_to_id'");
}

if (empty($ran_migration)) {
	// Overwrite 0's for records that reference an actual program
	Database::query("
		UPDATE sss_permissions
		SET program_id = (
			SELECT {$top} id
			FROM sss_programs
			WHERE LOWER(short_name) = LOWER(sss_permissions.category)
			{$limit}
		)
	");

	Database::query("
		UPDATE sss_events
		SET program_id = (
			SELECT {$top} id
			FROM sss_programs
			WHERE LOWER(short_name) = LOWER(sss_events.category)
			{$limit}
		)
	");


	Database::query("
		UPDATE sss_event_instances
		SET program_id = (
			SELECT {$top} id
			FROM sss_programs
			WHERE LOWER(short_name) = LOWER(sss_event_instances.program)
			{$limit}
		)
	");

	// Some sites have sped permissions with the category set to "Special Education" instead of "sped"
	// This gets past the previous update queries so it needs to be handled separately.
	$spedId = Database::get("SELECT id FROM sss_programs WHERE short_name = 'sped' OR name = 'Special Education'");
	if (!empty($spedId)) {
		$params = [ 'sped_id' => $spedId[0]['ID'] ];
		$query  = "
			UPDATE sss_permissions SET category = 'sped', program_id = :sped_id
			WHERE category = 'Special Education'
		";

		Database::query($query, $params);
	}

	// Any program ids that are still null are system permissions
	Database::query("UPDATE sss_permissions SET program_id = 0 WHERE program_id IS NULL");

	if (Database::$type === 'postgres') {
		Database::query("
			UPDATE sss_permissions
			SET short_name = SUBSTR(short_name, STRPOS(short_name, '.') + 1)
		");
	} else {
		Database::query("
			UPDATE sss_permissions
			SET short_name = SUBSTRING(short_name, CHARINDEX('.', short_name), LEN(short_name))
		");
	}
}
