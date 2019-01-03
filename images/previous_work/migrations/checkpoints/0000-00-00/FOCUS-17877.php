<?php

if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

$table = 'store_item';

if(!Database::tableExists($table)) {
	return false;
}

$column = 'start_date';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'DATE');
}

$column = 'end_date';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'DATE');
}