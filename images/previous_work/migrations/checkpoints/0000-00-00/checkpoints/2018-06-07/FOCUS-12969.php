<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql         =
	"SELECT
		MAX(sort_order) AS sort
	FROM
		gl_contact_types";
$res         = Database::get($sql);
$next_sort   = $res[0]["SORT"] + 1;
$district    = Facility::getDistrictFacility();
$name        = $district->getName();
$phone       = $district->getPhone();
$phone       = preg_replace("/[^\d]+/", "", $phone);
$phone_one   = substr($phone, 0, 3);
$phone_two   = substr($phone, 3, 3);
$phone_three = substr($phone, -4);

(new ContactType)
	->setTitle("ACH Email")
	->setSortOrder($next_sort)
	->persist();

(new Setting)
	->setKey("ach_vendor_email_template")
	->setValue("{{VENDOR_NAME}},<br><br>On {{deposit_date}} an electronic payment will be deposited to {{VENDOR_BANK_NAME}} in account {{VENDOR_ACCOUNT_NUMBER_MASKED}}. This payment is for check number {{CHECK_NUMBER}} and covers the following:<br><br>{{INVOICE_TABLE}}<br><br>Check amount: {{CHECK_AMOUNT}}<br><br>This payment was issued by {$name}. Please contact the finance office at ({$phone_one}) {$phone_two}-{$phone_three} should questions arise concerning this payment.<br><br>***** THIS IS AN AUTOMATED MESSAGE. PLEASE DO NOT REPLY. *****")
	->persist();

Database::commit();
return true;
?>