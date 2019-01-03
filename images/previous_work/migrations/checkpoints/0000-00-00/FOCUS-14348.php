<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

class MigrationFOCUS14348 {
	public static function createObjects($table, $columns, $required = [], $skip_adding_seq = false) {
		if (!Database::tableExists($table)) {
			Database::query("CREATE TABLE {$table} (tmp int)");
		}

		foreach ($columns as $column => $type) {
			if (!Database::columnExists($table, $column)) {
				$null = $column === 'id' ? false : !in_array($column, $required);

				Database::createColumn($table, $column, $type, '', $null);

				if ($column === 'id' && !$skip_adding_seq) {
					$sequence_name = "{$table}_{$column}_seq";

					if (!Database::sequenceExists($sequence_name)) {
						Database::createSequence($sequence_name);
					}

					if (Database::getPrimaryKey($table) === null) {
						Database::query("ALTER TABLE {$table} ADD PRIMARY KEY ({$column})");
					}
				}
			}
		}

		if (Database::columnExists($table, 'tmp')) {
			Database::query("ALTER TABLE {$table} DROP COLUMN tmp");
		}
	}
}

if(!Database::columnExists('gl_ar_funding_source', 'voucher')) {
	Database::createColumn('gl_ar_funding_source', 'voucher', 'smallint');
}

if(!Database::columnExists('ps_fees', 'voucher_sources')) {
	Database::createColumn('ps_fees', 'voucher_sources', 'text');
}

if(!Database::columnExists('gl_pos_deferral', 'created_by_class')) {
	Database::createColumn('gl_pos_deferral', 'created_by_class', 'VARCHAR', '128');
}

Database::query("UPDATE gl_pos_deferral SET created_by_class = 'ERPUser' WHERE created_by_class IS NULL");

MigrationFOCUS14348::createObjects('gl_ar_student_vouchers', [
	'id'                => 'bigint',
	'student_id'        => 'bigint',
	'school_id'         => 'bigint',
	'funding_source_id' => 'bigint',
	'amount'            => 'numeric',
	'start_date'        => 'date',
	'end_date'          => 'date',
],
['id', 'student_id', 'funding_source_id', 'amount', 'start_date', 'end_date']);
