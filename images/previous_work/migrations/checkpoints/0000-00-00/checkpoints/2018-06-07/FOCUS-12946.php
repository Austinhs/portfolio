<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$type    = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";
$stamp   = (Database::$type === "mssql") ? "CURRENT_TIMESTAMP" : "NOW()";
$warning = (Database::$type === "mssql") ? "SET ANSI_WARNINGS OFF " : "";
$classes = [
	"Facility",
	"FixedAsset",
	"AssetCategory",
	"Term",
	"CommodityItem",
	"CommodityClass",
	"WarehouseUOM",
	"WarehouseItem",
	"WarehousePoolProfile",
	"WarehousePool",
	"WarehouseType"
];

foreach ($classes as $class) {
	$id_field = strtolower($class::$idField);
	$table    = strtolower($class::$table);

	if (!Database::columnExists($table, "updated_at")) {
		Database::createColumn($table, "updated_at", $type);

		$params = [
			"class" => $class
		];
		$sql    =
			$warning . 
			"UPDATE
				{$table}
			SET
				updated_at =
					COALESCE
						(
							(
								SELECT
									MAX(log_time)
								FROM
									database_object_log l
								WHERE
									l.record_id = {$table}.{$id_field} AND
									l.record_class = :class AND
									l.log_time IS NOT NULL
							),
							{$stamp}
						)";

		Database::query($sql, $params);
	}
}

Database::commit();
return true;
?>