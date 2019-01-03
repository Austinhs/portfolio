<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$isPostgres = Database::$type === "postgres";
$keyText    = ($isPostgres) ? "PRIMARY KEY" : "NOT NULL PRIMARY KEY";

if (!Database::tableExists("gl_ba_bank_reconciliation_adjustment")) {
	Database::begin();

	$sql =
		"CREATE TABLE gl_ba_bank_reconciliation_adjustment (
			id BIGINT {$keyText},
			deleted BIGINT,
			title VARCHAR(255),
			amount NUMERIC(28, 10),
			bank BIGINT,
			ledger BIGINT,
			reconciliation_id BIGINT
		)";

	Database::query($sql);

	$sql = 
		"UPDATE 
			gl_ba_bank_reconciliation_adjustment 
		SET 
			id = {{next:gl_maint_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);
	Database::commit();
}

if (!Database::tableExists("gl_ba_bank_reconciliation_bank_connection")) {
	Database::begin();

	$sql =
		"CREATE TABLE gl_ba_bank_reconciliation_bank_connection (
			id BIGINT {$keyText},
			deleted BIGINT,
			primary_bank_id BIGINT,
			secondary_bank_id BIGINT
		)";

	Database::query($sql);

	$sql = 
		"UPDATE 
			gl_ba_bank_reconciliation_bank_connection 
		SET 
			id = {{next:gl_maint_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);
	Database::commit();
}

return true;
?>