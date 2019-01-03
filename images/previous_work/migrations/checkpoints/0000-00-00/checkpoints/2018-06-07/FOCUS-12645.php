<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_wh_picklist_item", "transaction_id")) {
	Database::createColumn("gl_wh_picklist_item", "transaction_id", "BIGINT");
}

Database::commit();
return true;
?>