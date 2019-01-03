<?php

MigrationFOCUS17901::run();

class MigrationFOCUS17901 {
	public static function run() {
		$ef_table = 'student_enrollment_form';

		self::createObjects(
			$ef_table,
			[
				'id'    => 'BIGINT',
				'title' => 'VARCHAR',
			]
		);

		self::normalizeApplicationLayoutColumns();

		self::correctSequences();

		$al_table = 'application_layout';
		$column   = 'student_enrollment_form_id';

		if(!Database::columnExists($al_table, $column)) {
			Database::createColumn($al_table, $column, 'BIGINT');

			Database::query("CREATE INDEX {$al_table}_{$column}_idx ON {$al_table} ({$column})");

			Database::query("
				ALTER TABLE {$al_table}
				ADD CONSTRAINT {$al_table}_{$column}_fk
				FOREIGN KEY ({$column})
				REFERENCES {$ef_table} (id)
				ON DELETE NO ACTION
				ON UPDATE CASCADE
			");
		}

		$has_new_online_app = !empty(Database::get("SELECT 1 FROM {$ef_table}"));

		if(!$has_new_online_app) {
			$query = Database::preprocess("
				INSERT INTO
					{$ef_table} (id, title)
				VALUES (
					{{next:{$ef_table}_seq}},
					'Online Application'
				)
			");

			Database::query($query);

			// Link any existing Online Applications and Layouts to the new table
			Database::query("UPDATE {$al_table} SET form = NULL, {$column} = (SELECT id FROM {$ef_table} WHERE title = 'Online Application') WHERE form = 0");
		}

		$app_table = 'application';
		$column    = 'application_layout_id';

		if(!Database::columnExists($app_table, $column)) {
			if(Database::getColumnType($al_table, 'application_id') !== 'bigint') {
				Database::changeColumnType($al_table, 'application_id', 'BIGINT');
			}

			$app_id_is_indexed = false;

			$indexes = Database::getIndexes($al_table);

			foreach($indexes as $idx_name => $cols) {
				if($cols[0] === 'application_id') {
					$app_id_is_indexed = true;
				}
			}

			if(!$app_id_is_indexed) {
				Database::query("CREATE INDEX {$al_table}_application_id_idx ON {$al_table} (application_id)");
			}

			Database::createColumn($app_table, $column, 'BIGINT');

			Database::query("
				UPDATE
					{$app_table}
				SET
					{$column} = (SELECT application_id FROM application_layout al WHERE al.form IS NULL AND language = {$app_table}.language)
			");

			Database::dropColumn($app_table, 'LANGUAGE');
			Database::dropColumn($app_table, 'FORM');
		}
	}

	private static function createObjects($table, $columns, $required = [], $skip_adding_seq = false) {
		if (!Database::tableExists($table)) {
			Database::query("CREATE TABLE {$table} (tmp int)");
		}

		foreach ($columns as $column => $type) {
			if (!Database::columnExists($table, $column)) {
				$null = $column === 'id' ? false : !in_array($column, $required);

				Database::createColumn($table, $column, $type, '', $null);

				if ($column === 'id' && !$skip_adding_seq) {
					$sequence_name = "{$table}_seq";

					if (!Database::sequenceExists($sequence_name)) {
						Database::createSequence($sequence_name);
					}

					if (Database::getPrimaryKey($table) === null) {
						Database::query("ALTER TABLE {$table} ADD PRIMARY KEY ({$column})");
					}
				}
			}
		}

		if (Database::columnExists($table, 'tmp')) {
			Database::query("ALTER TABLE {$table} DROP COLUMN tmp");
		}
	}

	/**
	 * It seems that the 'form' column on the application_layout table
	 * is not nullable on SQL Server, while it is nullable on Postgres.
	 * This method will normalize the column if the type or nullability
	 * is wrong.
	 */
	private static function normalizeApplicationLayoutColumns() {
		$table  = 'application_layout';
		$column = 'form';

		// The proper type for this column is BIGINT, as the values it
		// holds are in relation to the custom_field_categories:id column.
		$proper_type = 'bigint';

		$column_info      = Database::getColumns($table);
		$form_column_info = $column_info[$column];

		$is_not_nullable = $form_column_info['IS_NULLABLE'] !== 'YES';

		if($is_not_nullable || strtolower($form_column_info['DATA_TYPE']) !== $proper_type) {
			Database::changeColumnType($table, $column, $proper_type, '', true);
		}

		// Normalize the published column's nullability
		$column = 'published';

		if($column_info[$column]['IS_NULLABLE'] !== 'YES') {
			Database::changeColumnType($table, $column, 'VARCHAR', '1', true);
		}
	}

	private static function correctSequences() {
		$al_sequence = 'application_layout_seq';

		if(!Database::sequenceExists($al_sequence)) {
			$start = Database::get("
				SELECT
					MAX(application_id) + 1 AS start
				FROM
					application_layout
			")[0]['START'];

			Database::createSequence($al_sequence, $start);
		}
	}
}