<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$newFields = [
	"created_by"   => [
		"type"    => "BIGINT",
		"dbol"    => "user_id",
		"classes" => [
			"Invoice",
			"RunControls"
		]
	],
	"created_date" => [
		"type"    => "TIMESTAMP",
		"dbol"    => "log_time",
		"classes" => [
			"Invoice",
			"Check",
			"POSInvoice",
			"POSPayment",
			"POSReceipt"
		]
	]
];

Database::begin();

foreach ($newFields as $field => $data) {
	$type    = $data["type"];
	$classes = $data["classes"];
	$dbol    = $data["dbol"];

	foreach ($classes as $class) {
		$table   = $class::$table;
		$idField = $class::$idField;

		if (!Database::columnExists($table, $field)) {
			Database::createColumn($table, $field, $type);
		}

		$innerSql = db_limit(
			"SELECT
				{$dbol}
			FROM
				database_object_log
			WHERE
				record_id = {$table}.{$idField} AND
				record_class = '{$class}' AND
				action = 'INSERT'",
			1
		);
		$sql      =
			"UPDATE
				{$table}
			SET
				{$field} =
					(
						{$innerSql}
					)
			WHERE
				{$field} IS NULL";

		Database::query($sql);
	}
}

Database::commit();
return true;
?>