<?php

if(!FocusHelper::isFinanceEnabled()) {
	return false;
}

$table  = 'gl_ar_funding_source';
$column = 'active';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'smallint');

	Database::query("UPDATE {$table} SET active = 1");
}
