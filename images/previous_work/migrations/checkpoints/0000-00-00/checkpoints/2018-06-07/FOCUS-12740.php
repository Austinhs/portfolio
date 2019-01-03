<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql = 
	"DELETE FROM
		gl_fa_asset_allocation
	WHERE
		asset_id NOT IN
			(
				SELECT
					id
				FROM
					gl_fa_asset
			)";

Database::query($sql);

$sql = 
	"DELETE FROM
		gl_fa_asset_depreciation_allocation
	WHERE
		asset_id NOT IN
			(
				SELECT
					id
				FROM
					gl_fa_asset
			)";

Database::query($sql);
Database::commit();
return true;
?>