<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_wh_inventory_sheets")) {
	return false;
}

if (!Database::columnExists("gl_wh_inventory_sheets", "adjustment_type_id")) {
	Database::begin();
	Database::createColumn("gl_wh_inventory_sheets", "adjustment_type_id", "BIGINT");
	Database::commit();
}

return true;
?>