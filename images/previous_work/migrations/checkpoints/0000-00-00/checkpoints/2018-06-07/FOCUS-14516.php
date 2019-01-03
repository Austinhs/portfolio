<?php

if(file_exists(__DIR__ . '/../Warehouse.php')) {
	require_once(__DIR__ . '/../Warehouse.php');
}

Migrations::depend('FOCUS-11682');

Database::isolate(function() {
	if(!Database::columnExists('external_api', 'version')) {
		Database::createColumn('external_api', 'version', 'VARCHAR(255)');
		Database::query("UPDATE external_api SET version = '1.0' WHERE version IS NULL");
	}

	if(!Database::columnExists('external_api', 'dialect')) {
		Database::createColumn('external_api', 'dialect', 'VARCHAR(255)');
		Database::query("UPDATE external_api SET dialect = 'focus' WHERE dialect IS NULL");
	}

	if(!Database::columnExists('external_api', 'icon')) {
		Database::createColumn('external_api', 'icon', 'TEXT');
	}

	if(!Database::columnExists('external_api', 'deleted')) {
		Database::createColumn('external_api', 'deleted', 'BIGINT');
	}

	if(!Database::columnExists('external_api', 'deleted_at')) {
		Database::createColumn('external_api', 'deleted_at', 'TIMESTAMP');
	}

	// Assignments
	if(!Database::columnExists('gradebook_assignments', 'external_api_id')) {
		if(Database::$type === 'postgres') {
			Database::query("ALTER TABLE gradebook_assignments RENAME COLUMN external_api TO external_api_id");
		}
		else {
			Database::query("EXEC sp_rename 'gradebook_assignments.external_api', 'external_api_id', 'COLUMN'");
		}
	}

	if(!Database::columnExists('gradebook_assignments', 'external_api_uuid')) {
		if(Database::$type === 'postgres') {
			Database::query("ALTER TABLE gradebook_assignments RENAME COLUMN external_api_assignment TO external_api_uuid");
		}
		else {
			Database::query("EXEC sp_rename 'gradebook_assignments.external_api_assignment', 'external_api_uuid', 'COLUMN'");
		}
	}

	// API log
	if(!Database::tableExists('external_api_log')) {
		Database::query('CREATE TABLE external_api_log (id BIGINT PRIMARY KEY)');
	}

	if(!Database::sequenceExists('external_api_log_seq')) {
		Database::createSequence('external_api_log_seq');
	}

	if(!Database::columnExists('external_api_log', 'external_api_id')) {
		Database::createColumn('external_api_log', 'external_api_id', 'BIGINT');
	}

	if(!Database::indexExists('external_api_log', 'external_api_log_external_api_id')) {
		Database::query("CREATE INDEX external_api_log_external_api_id ON external_api_log (external_api_id)");
	}

	if(!Database::columnExists('external_api_log', 'request_date')) {
		Database::createColumn('external_api_log', 'request_date', 'TIMESTAMP');
	}

	if(!Database::columnExists('external_api_log', 'version')) {
		Database::createColumn('external_api_log', 'version', 'VARCHAR(255)');
	}

	if(!Database::columnExists('external_api_log', 'dialect')) {
		Database::createColumn('external_api_log', 'dialect', 'VARCHAR(255)');
	}

	if(!Database::columnExists('external_api_log', 'method')) {
		Database::createColumn('external_api_log', 'method', 'VARCHAR(255)');
	}

	if(!Database::columnExists('external_api_log', 'route')) {
		Database::createColumn('external_api_log', 'route', 'TEXT');
	}

	if(!Database::columnExists('external_api_log', 'headers')) {
		Database::createColumn('external_api_log', 'headers', 'TEXT');
	}

	if(!Database::columnExists('external_api_log', 'get')) {
		Database::createColumn('external_api_log', 'get', 'TEXT');
	}

	if(!Database::columnExists('external_api_log', 'post')) {
		Database::createColumn('external_api_log', 'post', 'TEXT');
	}

	if(!Database::columnExists('external_api_log', 'status')) {
		Database::createColumn('external_api_log', 'status', 'BIGINT');
	}

	if(!Database::columnExists('external_api_log', 'debug')) {
		Database::createColumn('external_api_log', 'debug', 'TEXT');
	}

	if(!Database::columnExists('external_api_log', 'error')) {
		Database::createColumn('external_api_log', 'error', 'TEXT');
	}

	if(Database::$type === 'postgres') {
		Database::query("
			CREATE OR REPLACE FUNCTION setUpdatedAt()
				RETURNS TRIGGER AS $$
				BEGIN
					NEW.updated_at := NOW();
					RETURN NEW;
				END
				$$
			LANGUAGE plpgsql;
		");
	}

	$uuids = [
		'address',
		'students_join_address',
		'students_join_people',
		'people_join_contacts',
	];

	foreach($uuids as $table) {
		Database::isolate(function() use($table) {
			set_time_limit(5 * 60);

			$uuid_function = Database::$type === 'postgres' ? 'uuid_generate_v4()' : 'newid()';

			if(!Database::columnExists($table, 'uuid')) {
				Database::createColumn($table, 'uuid', 'VARCHAR(255)');
			}

			$default = Database::get("SELECT column_default FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'uuid' AND LOWER(column_default) LIKE '%{$uuid_function}%'");

			if(empty($default)) {
				if(Database::$type === 'postgres') {
					Database::query("ALTER TABLE {$table} ALTER COLUMN uuid SET DEFAULT {$uuid_function}");
				}
				else {
					Database::query("ALTER TABLE {$table} ADD DEFAULT {$uuid_function} FOR uuid");
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
						uuid = {$uuid_function}
					{{mssql: OUTPUT 1 INTO {$output_table}}}
					WHERE
						uuid IS NULL
						{{postgres: AND ctid IN (SELECT ctid FROM {$table} WHERE uuid IS NULL LIMIT {$chunk_size})}}
					{{postgres: RETURNING 1 AS updated}}
				"));

				if(Database::$type === 'mssql') {
					$tmp = Database::get("SELECT TOP (1) updated FROM {$output_table}");
				}
			}
			while(!empty($tmp));

			if(!Database::indexExists($table, "{$table}_uuid_unique")) {
				Database::query("CREATE UNIQUE INDEX {$table}_uuid_unique ON {$table} (uuid)");
			}
		});
	}

	$updated_at = [
		'address',
		'gradebook_assignments',
		'gradebook_assignment_types',
		'attendance_calendars',
		'attendance_calendar',
		'people',
		'courses',
		'schedule',
		'gradebook_grades',
		'grad_subject_programs',
		'school_years',
		'school_semesters',
		'school_quarters',
		'master_courses',
		'schools',
		'student_enrollment',
		'course_periods',
		'students',
		'students_join_address',
		'students_join_users',
		'students_join_people',
		'users',
		'people_join_contacts',
	];

	foreach($updated_at as $table) {
		$index_name   = "{$table}_updated_at";
		$trigger_name = "{$table}_updated_at_trigger";

		if(!Database::columnExists($table, 'updated_at')) {
			Database::createColumn($table, 'updated_at', 'TIMESTAMP', '0');
		}

		if(!Database::indexExists($table, $index_name)) {
			Database::query("CREATE INDEX {$index_name} ON {$table} (updated_at)");
		}

		if(Database::$type === 'postgres') {
			Database::query("
				DO $$ BEGIN
					IF NOT EXISTS (SELECT NULL FROM information_schema.triggers i WHERE i.trigger_name = '{$trigger_name}') THEN
						CREATE TRIGGER {$trigger_name} BEFORE INSERT OR UPDATE ON {$table} FOR EACH ROW EXECUTE PROCEDURE setUpdatedAt();
					END IF;
				END $$;
			");
		}
		else {
			$exists = Database::get("SELECT NULL FROM sys.triggers WHERE name = '{$trigger_name}'");

			if(empty($exists)) {
				$on_clause = 'i.uuid = t.uuid';

				if($table === 'gradebook_grades') {
					$on_clause = 'i.assignment_id = t.assignment_id AND i.student_id = t.student_id';
				}

				Database::query("
					CREATE TRIGGER {$trigger_name} ON {$table}
					FOR INSERT, UPDATE
					AS
						UPDATE
							t
						SET
							t.updated_at = CURRENT_TIMESTAMP
						FROM
							{$table} t
						JOIN
							inserted i ON
							{$on_clause}
				");
			}
		}
	}
});
