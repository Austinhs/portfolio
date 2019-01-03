<?php

$table_name  = 'test_history_administrations';
$column_name = 'program_number';
$type        = 'VARCHAR';
$length      = '20';
$exists      = Database::columnExists($table_name, $column_name);

if (!$exists) {
	Database::createColumn($table_name, $column_name, $type, $length);
}
?>