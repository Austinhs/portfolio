<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

function getIndexes($table) {
	$res = Database::get("
		SELECT c.name AS column_name, i.name AS index_name
		FROM   sys.indexes i
		JOIN   sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
		JOIN   sys.columns c ON i.object_id = c.object_id AND ic.column_id = c.column_id
		JOIN   sys.objects t ON i.object_id = t.object_id
		WHERE  t.type IN ('U', 'UQ')
		AND    t.name = '{$table}'"
	);
	$retarr = [];

	foreach ($res as $r) {
		if (!isset($retarr[$r["INDEX_NAME"]])) {
			$retarr[$r["INDEX_NAME"]] = [];
		}

		$retarr[$r["INDEX_NAME"]][] = $r["COLUMN_NAME"];
	}

	return $retarr;
}

function dropIndexes($table, $indexes, $column) {
	foreach ($indexes as $name => $columns) {
		if (in_array($column, $columns)) {
			Database::query("DROP INDEX {$name} ON {$table}");
		}
	}
}

function createIndexes($table, $indexes, $column) {
	foreach ($indexes as $name => $columns) {
		if (in_array($column, $columns)) {
			Database::query("CREATE INDEX {$name} ON {$table} (" . implode(", ", $columns) . ")");
		}
	}
}

function checkColumn($table, $column, $type) {
	if (Database::$type == "mssql") {
		$indexes = getIndexes($table);
		dropIndexes($table, $indexes, $column);
	}

	if (!Database::columnExists($table, $column)) {
		Database::query("ALTER TABLE {$table} ADD {$column} {$type}");
	} else {
		if ($type != "VARCHAR") {
			checkColumn($table, $column, "VARCHAR");
		}

		$kywd = (Database::$type == "postgres" ? "TYPE" : "");
		$cast = (Database::$type == "postgres" ? "USING CAST({$column} AS {$type})" : "");

		Database::query("ALTER TABLE {$table} ALTER COLUMN {$column} {$kywd} {$type} {$cast}");
	}

	if (Database::$type == "mssql") {
		if ($type != "TEXT") {
			createIndexes($table, $indexes, $column);
		}
	}
}

Database::begin();

checkColumn("gl_hr_employment_contract", "contract_status", "TEXT");
checkColumn("gl_hr_employment_contract", "pay_type_id", "TEXT");
checkColumn("gl_hr_employment_contract", "facility_id", "TEXT");
checkColumn("gl_hr_employment_contract_assignment", "contract_status", "BIGINT");

Database::commit();

return true;
