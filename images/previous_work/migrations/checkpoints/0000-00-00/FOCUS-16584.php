<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

MigrationFOCUS16584::run();

class MigrationFOCUS16584 {
	public static function run() {
		self::addVoidedByClassColumn('gl_pos_receipt');
		self::addVoidedByClassColumn('gl_pos_receipt_allocation');
		self::addVoidedByClassColumn('gl_pos_receipt_line');
		self::addVoidedByClassColumn('gl_pos_payment');
	}

	private static function addVoidedByClassColumn($table) {
		$column = 'voided_by_class';
		$type   = 'varchar';
		$length = '32';

		if(!Database::columnExists($table, $column)) {
			Database::createColumn($table, $column, $type, $length);

			Database::query("UPDATE {$table} SET {$column} = 'ERPUser' WHERE voided_by IS NOT NULL");
		}
	}
}