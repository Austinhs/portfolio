<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql =
	"UPDATE
		gl_ba_bank_reconciliation_data
	SET
		source_class = NULL,
		source_id = NULL,
		exclude_from_balances = 1
	FROM
		gl_ba_bank_reconciliation r
	WHERE
		r.id = gl_ba_bank_reconciliation_data.reconciliation_id AND
		COALESCE(r.deleted, gl_ba_bank_reconciliation_data.deleted, r.finalized, 0) = 0 AND
		gl_ba_bank_reconciliation_data.source_class = 'Check' AND
		gl_ba_bank_reconciliation_data.source_id = -1";

Database::query($sql);
Database::commit();
return true;
?>