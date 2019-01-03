<?php

/**
 * @author Tom Wilson <tomw@focusschoolsoftware.com>
 */

class MigrationFOCUS15096 {
	const OLD_PATTERN = '/<button[^>]*?\s_field_column\s*=\s*\"([^"]+)\"[\s\S]*?>(.+?)<\/button>/i';
	const NEW_PATTERN = '[[$1::$2]]';

	public static $tables_and_columns = [
		'header_templates' => [
			'columns' => [
				'header',
				'footer',
				'side',
			],

			'primary_key' => 'id',
		],

		'letters' => [
			'columns' => [
				'body',
			],

			'primary_key' => 'id',
		],
	];

	public static function run() {
		if(Database::tableExists('gl_hr_employment_contract_template')) {
			self::$tables_and_columns['gl_hr_employment_contract_template'] = [
				'columns' => [
					'body',
				],

				'primary_key' => 'id',
			];
		}

		foreach(self::$tables_and_columns as $table => $info) {
			$primary_key = strtoupper($info['primary_key']);
			$columns     = $info['columns'];

			$columns_str = join(', ', array_merge($columns, [$primary_key]));

			$select_sql = "SELECT {$columns_str} FROM {$table}";

			$results = Database::get($select_sql);

			$update_col_strings = [];

			foreach($results as $result) {
				$params = [];

				if(empty($result[$primary_key])) {
					continue;
				}

				$id = $result[$primary_key];

				foreach($columns as $column) {
					$column = strtoupper($column);

					if(!isset($update_col_strings[$column])) {
						$update_col_strings[$column] = "{$column} = :{$column}";
					}

					$params[$column] = preg_replace(self::OLD_PATTERN, self::NEW_PATTERN, $result[$column]);
				}

				Database::query("UPDATE {$table} SET " . join(', ', $update_col_strings) . " WHERE {$primary_key} = {$id}", $params);
			}
		}
	}
}

MigrationFOCUS15096::run();
