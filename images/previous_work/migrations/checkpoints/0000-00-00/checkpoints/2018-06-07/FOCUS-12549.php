<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$isTenDigitVendor    = intval(Settings::get('ten_digit_vendors')) > 0;
$defaultVendorLength = $isTenDigitVendor ? '10' : '9';

$sql = "
	DELETE FROM gl_setting
	WHERE \"key\" = 'ten_digit_vendors'
";

Database::query($sql);

(new Setting())
	->setKey('vendor_number_length')
	->setValue($defaultVendorLength)
	->setJson(0)
	->persist();

Database::commit();

return true;
