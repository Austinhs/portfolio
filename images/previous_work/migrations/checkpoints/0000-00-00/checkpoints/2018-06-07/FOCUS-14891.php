<?php

if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

$table  = 'gl_pos_merchant_account';
$column = 'settlement_time';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'TIME', 0);
}

$table  = 'gl_pos_cashout';
$column = 'transactions_start_date';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'TIMESTAMP', 0);
}

$column = 'transactions_end_date';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'TIMESTAMP', 0);
}

Database::query("UPDATE gl_pos_cashout SET transactions_end_date = cashout_date");

if(Database::columnExists($table, 'forced_date')) {
	if(Database::$type === 'mssql') {
		$start_date_sql = "CAST(CONCAT(CAST(cashout_date AS DATE), ' 00:00:00') AS DATETIME2)";
	}
	elseif(Database::$type === 'postgres') {
		$start_date_sql = "DATE_TRUNC('day', cashout_date)";
	}

	$sql = "
		UPDATE
			gl_pos_cashout
		SET
			transactions_start_date = {$start_date_sql}
		WHERE
			CAST(cashout_date AS VARCHAR) LIKE '%23:59:59%'
			AND forced_date = 1
	";

	Database::query($sql);
}

// This branch eliminates the ability to have users assigned to portal-enabled cash drawers
// Remove any users that might be assigned to portal-enabled cash drawers at this point
Database::query("
	DELETE FROM
		gl_pos_cash_drawer_assignment
	WHERE
		cash_drawer_id IN (SELECT id FROM gl_pos_cash_drawer WHERE portal_enabled = 1)
");
