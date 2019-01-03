<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("ps_fee_history", "mp_short_name")) {
	Database::createColumn("ps_fee_history", "mp_short_name", "VARCHAR");
}

Database::commit();
return true;
?>