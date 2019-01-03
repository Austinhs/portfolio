<?php

Migrations::depend('FOCUS-6359');

Database::query("
	UPDATE
		user_permission
	SET
		\"key\" = 'AuditTrail/StudentAuditTrail.php'
	WHERE
		\"key\" = 'Students/AuditTrail.php'
");

Database::query("
	UPDATE
		user_permission
	SET
		\"key\" = 'AuditTrail/UserAuditTrail.php'
	WHERE
		\"key\" = 'Users/AuditTrail.php'
");

// Change user_permission.user_id to a BIGINT
$test_sql = Database::preprocess("
	SELECT
		COUNT(*) AS n
	FROM
		user_permission
	WHERE
		NOT ({{is_int:user_id}})
");

$test_rows = Database::get($test_sql);

if(!empty($rows)) {
	Database::query("
		UPDATE
			user_permission
		SET
			user_id = SUBSTRING(user_id, 2, 999999)
		WHERE
			SUBSTRING(user_id, 1, 1) = 'U'
	");

	if(Database::$type === 'mssql') {
		// Drop indexes on 'user_id'
		$indexes = Database::getIndexes('user_permission');

		foreach($indexes as $index => $columns) {
			foreach($columns as $column) {
				if(strtolower($column) === 'user_id') {
					Database::query("DROP INDEX {$index} ON user_permission");
					break;
				}
			}
		}
	}

	Database::changeColumnType('user_permission', 'user_id', 'BIGINT');

	if(Database::$type === 'mssql') {
		// Recreate an index on user_id
		Database::query("
			CREATE UNIQUE INDEX
				user_permission_ind1
			ON
				user_permission (user_id, \"key\")
		");
	}
}

// Drop the value column
if(Database::columnExists('user_permission', 'value')) {
	Database::query("
		DELETE FROM
			user_permission
		WHERE
			value = 0
	");

	if(Database::$type === 'mssql') {
		// Drop indexes on 'value'
		$indexes = Database::getIndexes('user_permission');

		foreach($indexes as $index => $columns) {
			foreach($columns as $column) {
				if(strtolower($column) === 'value') {
					Database::query("DROP INDEX {$index} ON user_permission");
					break;
				}
			}
		}
	}

	Database::query("
		ALTER TABLE
			user_permission
		DROP COLUMN
			value
	");
}
