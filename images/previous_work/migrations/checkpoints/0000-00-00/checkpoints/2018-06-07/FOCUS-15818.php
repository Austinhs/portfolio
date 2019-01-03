<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("ps_fees", "debit_account_id")) {
	Database::createColumn("ps_fees", "debit_account_id", "BIGINT");
}

if (!Database::columnExists("ps_fees", "credit_account_id")) {
	Database::createColumn("ps_fees", "credit_account_id", "BIGINT");
}

$ar_debit  = Setting::getOne([
	"\"key\" = 'ar_receipt_a'"
]);
$ar_credit = Setting::getOne([
	"\"key\" = 'ar_receipt_b'"
]);

if (!$ar_debit) {
	$ar_debit = (new Setting)
		->setKey("ar_receipt_a");
}

if (!$ar_credit) {
	$ar_credit = (new Setting)
		->setKey("ar_receipt_b");
}

$ar_debit
	->setValue(Settings::get("ar_invoice_a"))
	->persist();
$ar_credit
	->setValue(Settings::get("ar_invoice_b"))
	->persist();
Database::commit();
return true;
?>