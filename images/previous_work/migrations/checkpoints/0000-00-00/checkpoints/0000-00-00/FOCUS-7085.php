<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

if(!Database::columnExists(FAAssetAllocation::$table, 'start_date')) {
	$database_type = Database::$type;
	$column_type   = $database_type == 'mssql' ? 'datetime' : 'timestamp';

	Database::createColumn(FAAssetAllocation::$table, 'start_date', $column_type);
	Database::createColumn(FAAssetAllocation::$table, 'end_date', $column_type);
}

	$query = "
	UPDATE
		gl_fa_asset_allocation
	SET
		start_date = asset.date_acquired,
		end_Date = asset.date_disposition
	FROM
		gl_fa_Asset asset
	WHERE
		asset.id = asset_id
		and (start_date is null
		or end_date is null)
	";

Database::query($query);
