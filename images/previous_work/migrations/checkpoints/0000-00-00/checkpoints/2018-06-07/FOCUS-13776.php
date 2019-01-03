<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_ba_bank_reconciliation_ignored_checks")) {
	$sql =
		"CREATE TABLE gl_ba_bank_reconciliation_ignored_checks (
			id BIGINT PRIMARY KEY,
			deleted INT,
			value VARCHAR(255),
			type VARCHAR(6)
		)";

	Database::query($sql);
}

Database::commit();
return true;