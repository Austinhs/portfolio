<?php

Migrations::depend('FOCUS-17004');

$table  = 'ps_fees';
$column = 'bill_per';

if(!Database::columnExists($table, $column) && Database::columnExists('ps_fees', 'flat_fee')) {
	Database::createColumn($table, $column, 'VARCHAR', '16');

	$bill_per = PsFees::BILL_PER_FLAT;

	Database::query("
		UPDATE
			ps_fees
		SET
			bill_per = '{$bill_per}'
		WHERE
			COALESCE(flat_fee, 0) = 1
	");

	$bill_per = PsFees::BILL_PER_HOUR;

	Database::query("
		UPDATE
			ps_fees
		SET
			bill_per = '{$bill_per}'
		WHERE
			COALESCE(flat_fee, 0) = 0 AND COALESCE(annual_fee, 0) = 0 AND COALESCE(one_time_fees, 0) = 0
	");
}

$column = 'free_reduced_amount';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'NUMERIC', '(28,10)');
}

$column = 'free_reduced_accounting_strip_id';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'BIGINT');
}

$column = 'free_reduced_accounting_strip_hash';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'VARCHAR', '255');
}

$table  = 'schedule';
$column = 'reauthorization_days';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'NUMERIC');
}

Database::query("UPDATE program_config SET value = UPPER(value) WHERE title = 'BILL_BY' AND value != 'None' AND value != 'use_system'");
