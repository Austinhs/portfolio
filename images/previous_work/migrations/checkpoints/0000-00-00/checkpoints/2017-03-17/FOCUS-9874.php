<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql =
	"SELECT 
		j.id AS mj_id,
		b.id AS bank_id
	FROM 
		gl_manual_journal j
	JOIN 
		gl_banks b
	ON 
		j.credit_account = b.account_id OR 
		j.credit_account = b.asset_account_id OR
		j.credit_account = b.liability_account_id OR
		j.debit_account = b.account_id OR
		j.debit_account = b.asset_account_id OR
		j.debit_account = b.liability_account_id
	WHERE 
		COALESCE(j.bank_id, 0) = 0 AND 
		COALESCE(j.deleted, 0) = 0";

$results = Database::get($sql);
$data    = [];

foreach ($results as $result) {
	$mjId   = $result["MJ_ID"];
	$bankId = $result["BANK_ID"];

	if (!$bankId) {
		continue;
	}

	if (!isset($data[$mjId])) {
		$data[$mjId] = [];
	}

	$data[$mjId][] = $bankId;
}

Database::begin();

foreach ($data as $mjId => $dataItem) {
	if (count($dataItem) >= 2) {
		continue;
	}

	$bankId = reset($dataItem);
	$sql    = 
		"UPDATE 
			gl_manual_journal
		SET 
			bank_id = {$bankId}
		WHERE
			id = {$mjId}";

	Database::query($sql);
}

Database::changeColumnType("gl_ba_bank_reconciliation_data", "check_number", "VARCHAR");

Database::commit();

return true;
?>