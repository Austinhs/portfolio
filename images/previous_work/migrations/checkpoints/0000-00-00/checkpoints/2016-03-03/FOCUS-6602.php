<?php

Migrations::depend('FOCUS-5468');
Migrations::depend('FOCUS-5470');

// This migration can only run on postgres with PDO
if(Database::$type === 'postgres' && Database::supportsPDO()) {
	// Isolate this code since VACUUM can't run in a transaction
	Database::isolate(function() {
		$tables = [
			'custom_fields',
			'custom_field_categories',
			'custom_fields_join_categories',
			'custom_field_categories_join_profiles',
			'custom_field_categories_join_schools',
			'custom_field_select_options',
			'custom_field_log_entries',
			'custom_field_log_columns',
		];

		foreach(['SISStudent', 'SISUser'] as $class) {
			$tables[] = $class::$table;
			$tables[] = $class::getFormTable();
		}

		foreach($tables as $table) {
			echo "Vaccuming {$table}\n\n";
			Database::query("VACUUM FULL {$table}");
		}
	});
}
