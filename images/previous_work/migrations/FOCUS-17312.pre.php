<?php

if(file_exists(__DIR__ . '/../Warehouse.php')) {
	require_once(__DIR__ . '/../Warehouse.php');

	// error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
	// ini_set('display_errors', 1);
}

$postgres = Database::$type === 'postgres';
$mssql    = Database::$type === 'mssql';
$schema   = $mssql ? 'dbo' : 'public';

// Add missing columns
if(!Database::columnExists('custom_fields', 'visible_syear_column')) {
	Database::createColumn('custom_fields', 'visible_syear_column', 'BIGINT');
}

// Fix bad data
if(Database::tableExists('external_api')) {
	Database::query("UPDATE external_api SET client_id = '00000000-0000-0000-0000-000000000000', client_secret = '00000000-0000-0000-0000-000000000000' WHERE CAST(client_id AS VARCHAR) = 'focus'");
}

if(Database::tableExists('florida_staffemail_initial')) {
	Database::query("UPDATE florida_staffemail_initial SET syear = NULL WHERE syear IS NOT NULL AND LTRIM(RTRIM(CAST(syear AS VARCHAR))) = ''");
}

Database::query("UPDATE scheduler_course_defaults SET course_weight = 1 WHERE course_weight IS NULL");
Database::query("UPDATE custom_field_select_options SET label = code WHERE label IS NULL");

Database::query("DELETE FROM attendance_period WHERE course_period_id IS NULL");
Database::query("DELETE FROM attendance_completed WHERE course_period_id IS NULL");
Database::query("DELETE FROM school_period_bell_times WHERE bell_schedule_id IS NULL");
Database::query("DELETE FROM standard_categories_2 WHERE parent_id IS NULL");
Database::query("DELETE FROM standard_categories_3 WHERE parent_id IS NULL");
Database::query("DELETE FROM standard_categories_4 WHERE parent_id IS NULL");
Database::query("DELETE FROM custom_field_select_options WHERE label IS NULL");

// Drop statistics
if($mssql) {
	$tmp = Database::get("SELECT OBJECT_NAME(object_id) AS table_name, name FROM sys.stats WHERE user_created = 1");

	foreach($tmp as $record) {
		$table_name = $record['TABLE_NAME'];
		$name       = $record['NAME'];

		Database::query("DROP STATISTICS \"{$table_name}\".\"{$name}\"");
	}
}

// Drop triggers
if($postgres) {
	$tmp = Database::get("
		SELECT DISTINCT
			triggers.event_object_table AS table_name,
			triggers.trigger_name AS trigger_name
		FROM
			information_schema.triggers triggers
		WHERE
			triggers.trigger_name LIKE '%_updated_at_trigger%' OR
			triggers.trigger_name LIKE '%f:t%'
	");
}
else {
	$tmp = Database::get("
		SELECT
			OBJECT_NAME(parent_obj) AS table_name,
			sysobjects.name AS trigger_name
		FROM
			sysobjects
		WHERE
			sysobjects.type = 'TR' AND
			(
				sysobjects.name LIKE '%_updated_at_trigger%' OR
				sysobjects.name LIKE '%f:t%'
			)
	");
}

foreach($tmp as $record) {
	$table_name = $record['TABLE_NAME'];
	$name       = $record['TRIGGER_NAME'];

	if($postgres) {
		Database::query("DROP TRIGGER \"{$name}\" ON \"{$table_name}\"");
	}
	else {
		Database::query("DROP TRIGGER \"{$name}\"");
	}
}

// Backfill UUIDs
foreach([
	'address',
	'attendance_calendar',
	'attendance_calendars',
	'attendance_codes',
	'course_periods',
	'courses',
	'discipline_incidents',
	'discipline_referrals',
	'grad_programs',
	'grad_subject_programs',
	'gradebook_assignment_types',
	'gradebook_assignments',
	'gradebook_grades',
	'grade_posting_schemes',
	'grade_posting_term_weights',
	'login_token',
	'master_courses',
	'people_join_contacts',
	'people',
	'progression_plan_categories',
	'resources',
	'schedule',
	'school_periods',
	'school_quarters',
	'school_semesters',
	'school_years',
	'schools',
	'student_enrollment',
	'students_join_address',
	'students_join_people',
	'students_join_users',
	'students',
	'user_enrollment',
	'users',
] as $table_name) {
	if(Database::columnExists($table_name, 'uuid')) {
		if($table_name === 'students') {
			$id_column = 'student_id';
		}
		else if($table_name === 'users') {
			$id_column = 'staff_id';
		}
		else {
			$id_column = Database::columnExists($table_name, 'id') ? 'id' : null;
		}

		Schema::setColumnValue($table_name, 'uuid', $postgres ? 'uuid_generate_v4()' : 'newid()', 'uuid IS NULL', $id_column);
	}
}

// Fix timestamps
if($postgres) {
	$tmp = Database::get("
		SELECT
			table_name,
			column_name
		FROM
			information_schema.columns
		WHERE
			table_schema = '{$schema}' AND
			data_type IN ('bigint', 'integer') AND
			(
				column_name IN ('updated_at', 'created_at') OR
				(table_name = 'gl_pos_invoice_allocation' AND column_name = 'cancelled_date')
			)
	");

	foreach($tmp as $record) {
		$table_name  = $record['TABLE_NAME'];
		$column_name = $record['COLUMN_NAME'];

		Database::query("ALTER TABLE {$table_name} ALTER COLUMN {$column_name} TYPE TIMESTAMP USING to_timestamp({$column_name})");
	}
}
else if($mssql) {
	$tmp = Database::get("
		SELECT
			table_name,
			column_name
		FROM
			information_schema.columns
		WHERE
			table_schema = '{$schema}' AND
			(
				data_type = 'timestamp' OR
				(
					data_type IN ('bigint', 'integer') AND
					column_name IN ('updated_at', 'created_at', 'data_table_test_detail')
				)
			)
	");

	foreach($tmp as $record) {
		$table    = $record['TABLE_NAME'];
		$column   = $record['COLUMN_NAME'];

		Database::begin();
		Database::query("ALTER TABLE {$table} ADD _{$column} DATETIME2");
		Database::query("UPDATE {$table} SET \"_{$column}\" = DATEADD(second, CAST(\"{$column}\" AS BIGINT), '1970-01-01')");
		Database::query("ALTER TABLE {$table} DROP COLUMN \"{$column}\"");
		Database::query("EXEC sp_rename '{$schema}.{$table}._{$column}', '{$column}', 'COLUMN'");
		Database::commit();
	}
}

if(Database::tableExists('gl_ap_request')) {
	$tmp     = Database::get("SELECT MAX(requisition_number) AS seq FROM gl_ap_request");
	$nextval = $mssql ? "NEXT VALUE FOR req_num_seq" : "nextval('req_num_seq')";
	$start   = 0;

	foreach($tmp as $record) {
		$start = intval($record['SEQ']);
	}

	$start += 1;

	Schema::createSequence('req_num_seq', $start);
	Database::query("UPDATE gl_ap_request SET requisition_number = {$nextval} WHERE EXISTS (SELECT NULL FROM gl_ap_request r2 WHERE r2.id < gl_ap_request.id AND r2.requisition_number = gl_ap_request.requisition_number)");
	Database::query("UPDATE gl_sequences SET seq = {$nextval} WHERE title = 'requisition_number'");
}


// Rename 'id' to something else on some tables
Schema::dropIndex('logging_fields_select_options', 'IX_StuID');

foreach([
	'logging_fields_select_options'  => 'legacy_id',
	'fas_test_data_answers_content'  => 'data_id',
	'fas_test_data_import_info'      => 'data_id',
	'fas_test_data_question_content' => 'data_id',
	'fas_test_data_standards'        => 'data_id',
	'fas_tests_sharing_custom'       => 'test_id',
	'fas_tests_sharing_type'         => 'test_id',
] as $table => $new_column) {
	if(Database::columnExists($table, 'id') && !Database::columnExists($table, $new_column)) {
		$sequence = "{$table}_seq";
		$nextval  = $mssql ? "NEXT VALUE FOR {$sequence}" : "nextval('{$sequence}')";

		Database::begin();
		Schema::createColumn($table, $new_column, 'BIGINT', null, true);
		Database::query("UPDATE {$table} SET {$new_column} = id");
		Schema::dropConstraint($table, "{$table}_id_fkey");
		Schema::dropIndex($table, "{$table}_id");
		Schema::dropIndex($table, "{$table}_id_idx");

		$tmp = Database::get("
			SELECT
				constraint_info.constraint_name AS name,
				constraint_info.table_name AS table_name
			FROM
				information_schema.table_constraints constraint_info
			JOIN
				information_schema.key_column_usage constraint_column ON
				constraint_column.table_name = constraint_info.table_name AND
				constraint_column.constraint_name = constraint_info.constraint_name
			WHERE
				constraint_info.constraint_schema = '{$schema}' AND
				constraint_info.table_name = '{$table}'
			ORDER BY
				CASE
					WHEN constraint_info.constraint_type = 'FOREIGN KEY' THEN 0
					WHEN constraint_info.constraint_type = 'CHECK' THEN 2
					WHEN constraint_info.constraint_type = 'PRIMARY KEY' THEN 3
					WHEN constraint_info.constraint_type = 'UNIQUE' THEN 4
					ELSE 1
				END
		");

		foreach($tmp as $record) {
			$name = $record['NAME'];

			Schema::dropIndex($table, $name);
		}

		Schema::dropColumn($table, 'id');
		Schema::createSequence($sequence);
		Database::query("ALTER TABLE {$table} ADD id BIGINT PRIMARY KEY DEFAULT {$nextval}");
		Database::commit();
	}
}

// Drop deprecated tables
$drop_tables = [
	'fas_test_data_matching_answers_content',
];

foreach($drop_tables as $table) {
	if(Database::tableExists($table)) {
		Database::query("DROP TABLE {$table}");
	}
}

// Sometimes this column is duplicated with a space at the end of its name
if(Database::tableExists('gl_pr_staff_job_positions')) {
	if(Database::columnExists('gl_pr_staff_job_positions', 'temp_position_code')) {
		Database::query("ALTER TABLE gl_pr_staff_job_positions DROP COLUMN IF EXISTS \"temp_position_code \"");
	}
	else if(Database::columnExists('gl_pr_staff_job_positions', 'temp_position_code ')) {
		Database::renameColumn('temp_position_code ', 'temp_position_code', 'gl_pr_staff_job_positions');
	}
}

// This column was declared as NUMERIC on some databases
$tmp = Database::get("SELECT 1 FROM information_schema.columns WHERE LOWER(table_name) = 'gl_wh_items' AND LOWER(column_name) = 'discontinued_date' AND LOWER(data_type) IN ('numeric', 'float')");

if(!empty($tmp)) {
	if($postgres) {
		Database::query("ALTER TABLE gl_wh_items ALTER COLUMN discontinued_date TYPE TIMESTAMP USING to_timestamp(discontinued_date)");
	}
	else {
		Database::renameColumn('discontinued_date', 'discontinued_date_backup', 'gl_wh_items');
		Database::createColumn('gl_wh_items', 'discontinued_date', 'TIMESTAMP');
	}
}

// Fix TEXT columns that should be BIGINT
$tables = [
	'gl_hr_employment_contract' => [
		'facility_id',
		'pay_type_id',
	],
];

foreach($tables as $table => $columns) {
	foreach($columns as $column) {
		$tmp = Database::get("SELECT 1 FROM information_schema.columns WHERE LOWER(table_name) = '{$table}' AND LOWER(column_name) = '{$column}' AND LOWER(data_type) IN ('text', 'varchar', 'char', 'character varying', 'character')");

		if(!empty($tmp)) {
			Database::begin();
			Database::renameColumn($column, "{$column}_tmp", $table);
			Database::createColumn($table, $column, 'BIGINT');
			if($postgres) {
				Database::query("UPDATE {$table} SET \"{$column}\" = CAST(\"{$column}_tmp\" AS BIGINT)");
			}
			else {
				Database::query("UPDATE {$table} SET \"{$column}\" = CAST(CAST(\"{$column}_tmp\" AS VARCHAR(255)) AS BIGINT)");
			}
			Database::commit();
		}
	}
}

// Drop SSS tables on databases that don't have it installed
if(!defined('SSS_ENABLED') || empty(SSS_ENABLED)) {
	$tmp = Database::get("
		SELECT
			constraint_info.constraint_name AS name,
			constraint_info.table_name AS table_name
		FROM
			information_schema.table_constraints constraint_info
		JOIN
			information_schema.key_column_usage constraint_column ON
			constraint_column.table_name = constraint_info.table_name AND
			constraint_column.constraint_name = constraint_info.constraint_name
		WHERE
			constraint_info.constraint_schema = '{$schema}' AND
			LOWER(constraint_info.table_name) LIKE 'sss__%'
		ORDER BY
			CASE
				WHEN constraint_info.constraint_type = 'FOREIGN KEY' THEN 0
				WHEN constraint_info.constraint_type = 'CHECK' THEN 2
				WHEN constraint_info.constraint_type = 'PRIMARY KEY' THEN 3
				WHEN constraint_info.constraint_type = 'UNIQUE' THEN 4
				ELSE 1
			END
	");

	foreach($tmp as $record) {
		$table = $record['TABLE_NAME'];
		$name  = $record['NAME'];

		Schema::dropConstraint($table, $name);
	}

	$tmp = Database::get("SELECT table_name FROM information_schema.tables WHERE LOWER(table_name) LIKE 'sss__%' ORDER BY table_name");

	foreach($tmp as $record) {
		$table = $record['TABLE_NAME'];

		Database::query("DROP TABLE {$table}");
	}
}

// Delete unnecessary data
Database::query("TRUNCATE TABLE runquery_log");
Database::query("DELETE FROM login_token WHERE CURRENT_TIMESTAMP > expiration");

// Delete duplicate records from tables
$dedupe = [
	'attendance_completed' => [
		'course_period_id',
		'period_id',
		'school_date',
		'staff_id',
	],

	'course_weights' => [
		'course_id',
		'course_weight',
	],

	'gradebook_grades' => [
		'student_id',
		'assignment_id',
		'course_period_id',
	],

	'gl_hr_demographic_old' => [
		'staff_id',
	],

	'schedule' => [
		'syear',
		'student_id',
		'course_id',
		'course_weight',
		'course_period_id',
		'start_date',
	],

	'schedule_enrollment_codes' => [
		'id',
	],

	'schedule_inclusion_details' => [
		'schedule_id',
	],

	'school_period_bell_schedules' => [
		'id',
	],

	'student_standard_grades' => [
		'student_id',
		'course_period_id',
		'marking_period_id',
		'standard_id',
	],

	'gl_budget' => [
		'year',
		'type',
		'accounting_strip_id',
	],

	'email_notifications' => [
		'user_id',
		'user_type',
	],

	'permission' => [
		'profile_id',
		'key',
	],

	'scheduler_followup_course' => [
		'followup_course_id',
	],
];

foreach($dedupe as $table_name => $columns) {
	if(Database::tableExists($table_name)) {
		Schema::deduplicate($table_name, $columns);
	}
}
