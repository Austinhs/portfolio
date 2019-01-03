<?php

Migrations::depend('FOCUS-9271b');

// Tags: SSS, Formbuilder
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

// Only run this if SSS was disabled when FOCUS-8295b ran
if (Database::columnExists('sss_form_triggers', 'form_field_id')) {
	$bindingConstraints = Database::getConstraints('sss_form_bindings');
	if (isset($bindingConstraints['sss_form_bindings_form_id_foreign'])) {
		Database::query("ALTER TABLE sss_form_bindings DROP CONSTRAINT sss_form_bindings_form_id_foreign");
	}

	// Some bindings are safe to delete because the form was never used on an event
	// and it was deleted. OR the form was used but the events were deleted and so
	// was the form.
	$query = db_limit("
		SELECT 1 FROM sss_forms
		WHERE sss_forms.id = sss_form_bindings.form_id
			AND new_form_id IS NOT NULL
	", 1);
	Database::query("
		DELETE FROM sss_form_bindings WHERE NOT EXISTS({$query})
	");

	$query = db_limit("SELECT f.new_form_id FROM sss_forms f WHERE f.id = sss_form_bindings.form_id", 1);
	Database::query("UPDATE sss_form_bindings SET form_id = ({$query})");

	Database::query("ALTER TABLE sss_form_bindings ADD CONSTRAINT sss_form_bindings_form_id_foreign FOREIGN KEY (form_id) REFERENCES formbuilder_forms(id);");

	$formTriggerConstraints = Database::getConstraints('sss_form_triggers');
	if (isset($formTriggerConstraints['sss_form_triggers_form_id_foreign'])) {
		Database::query("ALTER TABLE sss_form_triggers DROP CONSTRAINT sss_form_triggers_form_id_foreign");
	}

	Database::query("
		DELETE FROM sss_form_triggers WHERE NOT EXISTS(
			SELECT 1 FROM sss_forms
			WHERE sss_forms.id = sss_form_triggers.form_id
			AND new_form_id IS NOT NULL
		)
	");

	$query = db_limit("SELECT f.new_form_id FROM sss_forms f WHERE f.id = sss_form_triggers.form_id", 1);
	Database::query("UPDATE sss_form_triggers SET form_id = ({$query})");

	Database::query("ALTER TABLE sss_form_triggers ADD CONSTRAINT sss_form_triggers_form_id_foreign FOREIGN KEY (form_id) REFERENCES formbuilder_forms(id);");

	if (isset($formTriggerConstraints['sss_form_triggers_form_field_id_foreign'])) {
		Database::query("ALTER TABLE sss_form_triggers DROP CONSTRAINT sss_form_triggers_form_field_id_foreign");
	}

	Database::query("ALTER TABLE sss_form_triggers DROP COLUMN form_field_id");
}
