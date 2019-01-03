<?php

$table  = 'attendance_completed';
$column = 'created_at';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'TIMESTAMP', 0);

	if(Database::$type === 'postgres') {
		Database::query("ALTER TABLE {$table} ALTER {$column} SET DEFAULT CURRENT_TIMESTAMP");
	}
	elseif(Database::$type === 'mssql') {
		Database::query("ALTER TABLE {$table} ADD DEFAULT CURRENT_TIMESTAMP FOR {$column}");
	}

	Database::query("UPDATE {$table} SET {$column}=last_updated_date");
}
