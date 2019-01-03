<?php

if (Database::$type === "mssql") {
	return false;
}

if (Database::$type === 'postgres') {
	$timestamp = 'timestamp without time zone';
} else {
	$timestamp = 'datetime2(6)';
}

if (!Database::columnExists('focus_table_records', 'deleted_at')) {
	Database::createColumn('focus_table_records', 'deleted_at', $timestamp);
}

if (Database::columnExists('focus_tables', 'id_column')) {
	Database::dropColumn('focus_tables', 'id_column');
}

if (Database::columnExists('focus_tables', 'generated_columns')) {
	Database::dropColumn('focus_tables', 'generated_columns');
}

try {
	Database::query("ALTER TABLE focus_table_records ADD UNIQUE (table_name, record_id)");
} catch (\Exception $e) {

}
