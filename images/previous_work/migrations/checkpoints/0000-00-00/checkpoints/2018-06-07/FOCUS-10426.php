<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_fa_inventory_asset", "changed_by_user_id")) {
	Database::createColumn("gl_fa_inventory_asset", "changed_by_user_id", "BIGINT");
}
