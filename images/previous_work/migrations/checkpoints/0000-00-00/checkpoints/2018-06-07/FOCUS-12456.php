<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_fa_asset_disposition_codes", "status")) {
	Database::createColumn("gl_fa_asset_disposition_codes", "status", "CHAR");
}

if (!Database::columnExists("gl_fa_asset_disposition_codes", "generate_disposal_date")) {
	Database::createColumn("gl_fa_asset_disposition_codes", "generate_disposal_date", "INT");
}

Database::commit();
return true;
?>