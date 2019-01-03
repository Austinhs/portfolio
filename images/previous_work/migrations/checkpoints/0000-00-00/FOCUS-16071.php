<?php

// Monroe
if ($GLOBALS['ClientId'] == 6140005738) {
	$order = [
		'sss_domains',
		'sss_progress_codes',
		'sss_permissions',
		'sss_objective_procedure_codes',
		'sss_event_triggers',
		'sss_event_step_sequences',
		'sss_event_steps',
		'sss_events',
		'formbuilder_collections',
		'formbuilder_forms',
		'formbuilder_components',
		'formbuilder_revisions',
		'formbuilder_objects',
		'sss_programs'
	];

	foreach ($order as $table) {
		$primaryKey = Database::getPrimaryKey($table);

		if (empty($primaryKey)) {
			throw new \Exception("Expected table {$table} to have a primary key, but it doesn't.");
		}

		$where = '';
		if ($table === "formbuilder_objects") {
			$where = "AND NOT EXISTS (SELECT 1 FROM formbuilder_components WHERE focus_table_records.record_id IN (model_id, layout_id, options_id))";
		}

		Database::query("DELETE FROM {$table} WHERE {$primaryKey} IN (
			SELECT record_id
			FROM focus_table_records
			WHERE table_name = '{$table}'
			{$where}
			ORDER BY id ASC
		)");

		Database::query("DELETE FROM focus_tables WHERE table_name = '{$table}'");
		Database::query("DELETE FROM focus_table_records WHERE table_name = '{$table}'");

		if (Database::$type === "postgres") {
			Database::query("ALTER SEQUENCE focus_table_records_id_seq MINVALUE 0 START WITH 1");
			Database::query("SELECT setval('focus_table_records_id_seq', 0)");
		} else {
			Database::query("ALTER SEQUENCE focus_table_records_id_seq RESTART WITH 1");
		}
	}
}
