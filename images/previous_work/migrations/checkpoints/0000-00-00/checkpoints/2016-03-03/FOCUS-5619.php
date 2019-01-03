<?php

Migrations::depend('FOCUS-6359');

Database::query("
	UPDATE
		permission
	SET
		\"key\" = 'AuditTrail/StudentAuditTrail.php'
	WHERE
		\"key\" = 'Students/AuditTrail.php'
");

Database::query("
	UPDATE
		permission
	SET
		\"key\" = 'AuditTrail/UserAuditTrail.php'
	WHERE
		\"key\" = 'Users/AuditTrail.php'
");

if(Database::columnExists('permission', 'value')) {
	Database::query("
		DELETE FROM
			permission
		WHERE
			value = 0
	");

	// Drop indexes on 'value'
	if(Database::$type === 'mssql') {
		$indexes = Database::getIndexes('permission');

		foreach($indexes as $index => $columns) {
			foreach($columns as $column) {
				if(strtolower($column) === 'value') {
					Database::query("DROP INDEX {$index} ON permission");
					break;
				}
			}
		}
	}

	Database::query("
		ALTER TABLE
			permission
		DROP COLUMN
			value
	");
}
