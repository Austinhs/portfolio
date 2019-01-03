<?php

if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

class MigrationFOCUS14155 {
	public static function addCreatedByClassColumn($table) {
		$column = 'created_by_class';

		if (!Database::columnExists($table, $column)) {
			Database::createColumn($table, $column, 'VARCHAR(255)');
		}

		Database::query("UPDATE {$table} SET {$column} = 'ERPUser' WHERE {$column} IS NULL");
	}
}

MigrationFOCUS14155::addCreatedByClassColumn('gl_pos_customer_credit_account_transaction');

$table  = 'gl_pos_receipt_line';
$column = 'cardholder_name';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'VARCHAR(40)');
}
