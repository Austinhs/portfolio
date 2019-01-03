<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_ba_bank_reconciliation", "outstanding_debits_data")) {
	Database::createColumn("gl_ba_bank_reconciliation", "outstanding_debits_data", "TEXT");
}

Database::commit();
return true;
?>