<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_ap_invoice_line_item", "amount")) {
	Database::createColumn("gl_ap_invoice_line_item", "amount", "NUMERIC", "(28,10)");
}

if (!Database::columnExists("gl_ap_invoice_line_item", "debit_account_id")) {
	Database::createColumn("gl_ap_invoice_line_item", "debit_account_id", "BIGINT");
}

if (!Database::columnExists("gl_ap_invoice_line_item", "credit_account_id")) {
	Database::createColumn("gl_ap_invoice_line_item", "credit_account_id", "BIGINT");
}

if (!Database::columnExists("gl_ap_invoice_allocation", "created_from_line_item")) {
	Database::createColumn("gl_ap_invoice_allocation", "created_from_line_item", "INT");
}

Database::commit();
return true;
?>