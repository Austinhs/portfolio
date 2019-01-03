<?php 
$table_name  = 'grad_subjects';
$column_name = 'ahs_subject';
$type        = 'VARCHAR';
$length      = '1';
$exists      = Database::columnExists($table_name, $column_name);

if (!$exists) {
	Database::createColumn($table_name, $column_name, $type, $length);
}
