<?php

Migrations::depend('FOCUS-8295a');
Migrations::depend('FOCUS-8295b');
Migrations::depend('FOCUS-8295c');

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

$eventForeigns = Database::getForeignKeys('sss_events', 'program_id', false);
if (!isset($eventForeigns['sss_events_program_id_foreign'])) {
	Database::query("ALTER TABLE sss_events ADD CONSTRAINT sss_events_program_id_foreign FOREIGN KEY (program_id) REFERENCES sss_programs(id)");
}

Database::query("UPDATE sss_permissions SET program_id = null WHERE program_id = 0");
Database::query("ALTER TABLE sss_permissions ADD CONSTRAINT sss_permissions_program_id_foreign FOREIGN KEY (program_id) REFERENCES sss_programs(id)");

if (!Database::columnExists("sss_event_step_sequences", "form_id")) {
	Database::createColumn("sss_event_step_sequences", "form_id", "bigint");
	Database::query("ALTER TABLE sss_event_step_sequences ADD CONSTRAINT sss_event_step_sequences_form_id_foreign FOREIGN KEY (form_id) REFERENCES formbuilder_forms(id)");

	Database::query("
		UPDATE sss_event_step_sequences
		SET form_id = (
			".db_limit("SELECT form_id
			FROM sss_form_bindings
			WHERE table_name = 'sss_event_step_sequences'
				AND table_id = sss_event_step_sequences.id", 1)."
		)
	");
}
