<?php

if(file_exists(__DIR__ . '/../Warehouse.php')) {
	require_once(__DIR__ . '/../Warehouse.php');
}

if(Database::$type === 'postgres') {
	Database::isolate(function() {
		Database::query('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
	});
}

$tables = [
	'login_token' => [
		'type' => 'VARCHAR(255)',
	],

	'external_api' => [
		'title'              => 'VARCHAR(255)',
		'url'                => 'TEXT',
		'authentication_url' => 'TEXT',
		'client_id'          => 'UUID',
		'client_secret'      => 'UUID',
		'external_id'        => 'TEXT',
		'external_secret'    => 'TEXT',
	],

	'external_api_school' => [
		'external_api_id' => 'BIGINT',
		'school_id'       => 'BIGINT',
	],

	'gradebook_assignments' => [
		'uuid'                    => 'UUID',
		'external_api'            => 'BIGINT',
		'external_api_assignment' => 'VARCHAR(255)',
	],

	'gradebook_assignment_types' => [
		'uuid' => 'UUID',
	],

	'grad_programs' => [
		'uuid' => 'UUID',
	],

	'grad_subject_programs' => [
		'uuid' => 'UUID',
	],

	'attendance_calendars' => [
		'uuid' => 'UUID',
	],

	'attendance_calendar' => [
		'uuid' => 'UUID',
	],

	'students' => [
		'uuid' => 'UUID',
	],

	'students_join_users' => [
		'uuid' => 'UUID',
	],

	'student_enrollment' => [
		'uuid' => 'UUID',
	],

	'users' => [
		'uuid' => 'UUID',
	],

	'people' => [
		'uuid' => 'UUID',
	],

	'schedule' => [
		'uuid' => 'UUID',
	],

	'schools' => [
		'uuid' => 'UUID',
	],

	'school_quarters' => [
		'uuid' => 'UUID',
	],

	'school_semesters' => [
		'uuid' => 'UUID',
	],

	'school_years' => [
		'uuid' => 'UUID',
	],

	'courses' => [
		'uuid' => 'UUID',
	],

	'master_courses' => [
		'uuid' => 'UUID',
	],

	'course_periods' => [
		'uuid' => 'UUID',
	],
];

foreach($tables as $table => $columns) {
	Database::isolate(function() use($table, $columns) {
		set_time_limit(5 * 60);

		$uuid_function = Database::$type === 'postgres' ? 'uuid_generate_v4()' : 'newid()';

		if(!Database::tableExists($table)) {
			Database::query("CREATE TABLE {$table} (id BIGINT PRIMARY KEY)");
		}

		if(!Database::sequenceExists("{$table}_seq")) {
			Database::createSequence("{$table}_seq");
		}

		foreach($columns as $column => $type) {
			if(!Database::columnExists($table, $column)) {
				Database::createColumn($table, $column, $type === 'UUID' ? 'VARCHAR(255)' : $type);
			}

			if(strtoupper($type) === 'UUID') {
				$default_uuid = Database::get("SELECT column_default FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = '{$column}' AND LOWER(column_default) LIKE '%{$uuid_function}%'");

				if(empty($default_uuid)) {
					if(Database::$type === 'postgres') {
						Database::query("ALTER TABLE {$table} ALTER COLUMN {$column} SET DEFAULT {$uuid_function}");
					}
					else {
						Database::query("ALTER TABLE {$table} ADD DEFAULT {$uuid_function} FOR {$column}");
					}
				}

				$chunk_size = 100000;

				do {
					$output_table = '##__tmp_' . uniqid() . '__';

					if(Database::$type === 'mssql') {
						Database::query("CREATE TABLE {$output_table} (updated INTEGER)");
					}

					$tmp = Database::get(Database::preprocess("
						UPDATE {{mssql: TOP ({$chunk_size})}}
							{$table}
						SET
							{$column} = {$uuid_function}
						{{mssql: OUTPUT 1 INTO {$output_table}}}
						WHERE
							{$column} IS NULL
							{{postgres: AND ctid IN (SELECT ctid FROM {$table} WHERE {$column} IS NULL LIMIT {$chunk_size})}}
						{{postgres: RETURNING 1 AS updated}}
					"));

					if(Database::$type === 'mssql') {
						$tmp = Database::get("SELECT TOP (1) updated FROM {$output_table}");
					}
				}
				while(!empty($tmp));

				if(!Database::indexExists($table, "{$table}_{$column}_unique")) {
					Database::query("CREATE UNIQUE INDEX {$table}_{$column}_unique ON {$table} ({$column})");
				}
			}
		}
	});
}
