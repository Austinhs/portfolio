<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pos_refund", "voided")) {
	Database::createColumn("gl_pos_refund", "voided", "INT");
}

if (!Database::columnExists("gl_pos_refund", "voided_by")) {
	Database::createColumn("gl_pos_refund", "voided_by", "BIGINT");
}

if (!Database::columnExists("gl_pos_refund", "voided_date")) {
	Database::createColumn("gl_pos_refund", "voided_date", "TIMESTAMP");
}

if (!Database::columnExists("gl_pos_refund", "transaction_id")) {
	Database::createColumn("gl_pos_refund", "transaction_id", "BIGINT");
}

if (!Database::columnExists("gl_pos_refund", "merchant_account_id")) {
	Database::createColumn("gl_pos_refund", "merchant_account_id", "BIGINT");
}

Database::commit();
return true;
?>