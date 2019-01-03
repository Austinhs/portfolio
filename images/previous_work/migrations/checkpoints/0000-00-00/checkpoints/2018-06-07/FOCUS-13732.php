<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED != true) {
	return;
}

if (!Database::columnExists("sss_event_steps", "parameters")) {
	Database::createColumn("sss_event_steps", "parameters", "text");
}

if (!Database::tableExists("sss_steps_join_forms")) {
	Database::query("
		CREATE TABLE sss_steps_join_forms (
			id BIGINT PRIMARY KEY NOT NULL,
			form_id BIGINT,
			step_id BIGINT
	)");

	Database::createSequence("sss_steps_join_forms_id_seq");
	if (Database::$type === 'mssql') {
		Database::query("ALTER TABLE sss_steps_join_forms ADD DEFAULT NEXT VALUE FOR sss_steps_join_forms_id_seq for ID");
	}
	else {
		Database::query("ALTER TABLE sss_steps_join_forms ALTER COLUMN id SET DEFAULT nextval('sss_steps_join_forms_id_seq')");
	}

	Database::query("ALTER TABLE sss_steps_join_forms ADD CONSTRAINT sss_steps_join_forms_form_id_foreign FOREIGN KEY (form_id) REFERENCES formbuilder_forms(id)");
	Database::query("ALTER TABLE sss_steps_join_forms ADD CONSTRAINT sss_steps_join_forms_step_id_foreign FOREIGN KEY (step_id) REFERENCES sss_event_steps(id)");
}
