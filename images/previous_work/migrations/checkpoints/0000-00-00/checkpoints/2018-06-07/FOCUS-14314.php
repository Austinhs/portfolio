<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_fa_inventory_asset", "inventoried_method")) {
	Database::createColumn("gl_fa_inventory_asset", "inventoried_method", "VARCHAR", 255);

	$with          =
		"WITH
			inventoried AS (
				SELECT
					ROW_NUMBER() OVER
						(
							PARTITION BY
								asset_id
							ORDER BY
								inventory_date DESC
						) AS row_number,
					asset_id,
					id
				FROM
					gl_fa_inventory_asset
			)";
	$postgres_with = (Database::$type !== "mssql") ? $with : "";
	$mssql_with    = (Database::$type === "mssql") ? $with : "";
	$sql           =
		"{$postgres_with}
		SELECT
			i.id,
			a.inventoried_method
		FROM
			gl_fa_asset a
		JOIN
			inventoried i
		ON
			i.asset_id = a.id AND
			i.row_number = 1
		WHERE
			a.inventoried_method IS NOT NULL";
	$sql =
		"{$mssql_with}
		UPDATE
			gl_fa_inventory_asset
		SET
			inventoried_method = tmp.inventoried_method
		FROM
			({$sql}) tmp
		WHERE
			tmp.id = gl_fa_inventory_asset.id";

	Database::query($sql);
}

Database::commit();
return true;
?>