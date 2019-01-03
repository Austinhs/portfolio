<?php

if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

function addCreatedByClassColumn($table) {
	$column = 'created_by_class';

	if (!Database::columnExists($table, $column)) {
		Database::createColumn($table, $column, 'VARCHAR(255)');
	}

	Database::query("UPDATE {$table} SET {$column} = 'ERPUser' WHERE {$column} IS NULL");
}

addCreatedByClassColumn('gl_pos_invoice');
addCreatedByClassColumn('gl_pos_receipt');
addCreatedByClassColumn('gl_pos_payment');
