<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_fa_default_asset_depreciation_allocation")) {
	$sql =
		"CREATE TABLE gl_fa_default_asset_depreciation_allocation (
			id BIGINT PRIMARY KEY,
			deleted INT,
			accounting_strip_id BIGINT,
			accounting_strip_hash VARCHAR(255),
			percentage NUMERIC(28,10)
		)";

	Database::query($sql);
}

Database::commit();
return true;