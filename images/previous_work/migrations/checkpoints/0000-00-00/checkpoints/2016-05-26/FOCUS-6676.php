<?php

$tables = [
	'custom_field_log_entries',
	'students_join_users'
];

foreach($tables as $table) {
	if(Database::columnExists($table, 'deleted')) {
		Database::query("
			DELETE FROM
				{$table}
			WHERE
				COALESCE(deleted, 0) != 0
		");

		Database::query("
			ALTER TABLE
				{$table}
			DROP COLUMN
				deleted
		");
	}
}
