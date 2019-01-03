<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_ba_bank_reconciliation_error", "notes")) {
	Database::createColumn("gl_ba_bank_reconciliation_error", "notes", "TEXT");
}

Database::commit();
return true;
?>