<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$disable = Settings::get("disable_auto_close");

if ($disable) {
	return true;
}

Database::begin();
(new Setting)
	->setKey("auto_close_on_invoice")
	->setValue(1)
	->persist();
Database::commit();
return true;
?>