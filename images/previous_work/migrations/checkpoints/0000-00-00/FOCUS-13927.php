<?php

MigrationFOCUS13927::run();

class MigrationFOCUS13927 {
	public static function run() {
		self::createObjects(
			'html_partial',

			[
				'id'            => 'bigint',
				'system'        => 'smallint',
				'package'       => 'varchar',
				'title'         => 'varchar',
				'alias'         => 'varchar',
				'lang'          => 'varchar|2',
				'html'          => 'text',
				'available_for' => 'text',
				'deleted'       => 'smallint',
			],

			[
				'id',
				'package',
				'title',
				'alias',
				'lang',
			]
		);
	}

	private static function createObjects($table, $columns, $required = [], $skip_adding_seq = false) {
		if (!Database::tableExists($table)) {
			Database::query("CREATE TABLE {$table} (tmp int)");
		}

		foreach ($columns as $column => $type) {
			if (!Database::columnExists($table, $column)) {
				$null = $column === 'id' ? false : !in_array($column, $required);

				$length = '';

				if(strpos($type, '|') !== false) {
					list($type, $length) = explode('|', $type);
				}

				Database::createColumn($table, $column, $type, '', $null);

				if ($column === 'id' && !$skip_adding_seq) {
					$sequence_name = "{$table}_{$column}_seq";

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
}
