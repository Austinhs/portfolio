<?php

$table = 'positive_behaviors';
$column = 'created_comment';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'TEXT');
}

$column = 'spent_comment';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'TEXT');
}
