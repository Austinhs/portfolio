<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_ba_bank_reconciliation_balance")) {
	$sql =
		"CREATE TABLE gl_ba_bank_reconciliation_balance (
			id BIGINT PRIMARY KEY,
			reconciliation_id BIGINT,
			bank_id BIGINT,
			beginning NUMERIC(28, 10),
			ending NUMERIC(28, 10) NULL
		)";

	Database::query($sql);

	$sql =
		"INSERT INTO
			gl_ba_bank_reconciliation_balance
				(id, reconciliation_id, bank_id, beginning, ending)
		SELECT
			{{next:gl_maint_seq}},
			id,
			bank_id,
			beginning_balance,
			ending_balance
		FROM
			gl_ba_bank_reconciliation";
	$sql = Database::preprocess($sql);

	Database::query($sql);
}

if (!Database::columnExists("gl_ba_bank_reconciliation_data", "bank_id")) {
	Database::createColumn("gl_ba_bank_reconciliation_data", "bank_id", "BIGINT");

	$sql =
		"UPDATE
			gl_ba_bank_reconciliation_data
		SET
			bank_id = r.bank_id
		FROM
			gl_ba_bank_reconciliation r
		WHERE
			r.id = gl_ba_bank_reconciliation_data.reconciliation_id";

	Database::query($sql);
}

if (!Database::columnExists("gl_ba_bank_reconciliation_adjustment", "bank_id")) {
	Database::createColumn("gl_ba_bank_reconciliation_adjustment", "bank_id", "BIGINT");

	$sql =
		"UPDATE
			gl_ba_bank_reconciliation_adjustment
		SET
			bank_id = r.bank_id
		FROM
			gl_ba_bank_reconciliation r
		WHERE
			r.id = gl_ba_bank_reconciliation_adjustment.reconciliation_id";

	Database::query($sql);
}

if (Database::columnExists("gl_ba_bank_reconciliation", "bank_id")) {
	if (Database::$type === "mssql") {
		$sql = db_limit(
			"SELECT 
				i.name
			FROM 
				sys.indexes i
			JOIN 
				sys.index_columns ic 
			ON 
				ic.object_id = i.object_id AND 
				ic.index_id = i.index_id
			JOIN 
				sys.columns c 
			ON
				c.object_id = ic.object_id AND 
				c.column_id = ic.column_id 
			JOIN 
				sys.tables t 
			ON
				t.object_id = i.object_id
			WHERE 
				i.is_primary_key = 0 AND
				i.is_unique = 0 AND
				i.is_unique_constraint = 0 AND
				t.is_ms_shipped = 0 AND 
				t.name = 'gl_ba_bank_reconciliation' AND 
				c.name = 'bank_id'",
			1
		);
		$res = Database::get($sql);

		if ($res) {
			$index = $res[0]["NAME"];
			$sql   =
				"DROP INDEX
					{$index}
				ON
					gl_ba_bank_reconciliation";

			Database::query($sql);
		}
	}

	Database::changeColumnType("gl_ba_bank_reconciliation", "bank_id", "TEXT");
	Database::renameColumn("bank_id", "bank_ids", "gl_ba_bank_reconciliation");

	$sql =
		"UPDATE
			gl_ba_bank_reconciliation
		SET
			bank_ids = CONCAT('[\"', bank_ids, '\"]')";

	Database::query($sql);
}

if (Database::columnExists("gl_ba_bank_reconciliation", "beginning_balance")) {
	Database::dropColumn("gl_ba_bank_reconciliation", "beginning_balance");
}

if (Database::columnExists("gl_ba_bank_reconciliation", "ending_balance")) {
	Database::dropColumn("gl_ba_bank_reconciliation", "ending_balance");
}

if (!Database::columnExists("gl_ba_bank_reconciliation_data", "manual_clear")) {
	Database::createColumn("gl_ba_bank_reconciliation_data", "manual_clear", "INT");
}

Database::commit();
return true;
?>