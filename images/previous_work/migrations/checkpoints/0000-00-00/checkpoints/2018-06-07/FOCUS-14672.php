<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$maintSeq  = Database::nextSql("gl_maint_seq");

Database::begin();

$res = Database::get("SELECT 'x' FROM gl_import_tool_groups WHERE id = 6");

if (empty($res))
	Database::query("INSERT INTO gl_import_tool_groups (id, system, title) VALUES (6, 1, '3rd Party Integration')");

$res = Database::get("SELECT tool_id FROM gl_import_tools WHERE db_class = 'ImportFrontLinePC'");

if (empty($res))
{
	$res       = Database::get("SELECT MAX(tool_id) AS tool_id, MAX(sort_order) AS sort_order FROM gl_import_tools WHERE group_id = 6");
	$toolID    = intval($res[0]["TOOL_ID"]) + 10;
	$sortOrder = intval($res[0]["SORT_ORDER"]) + 10;

	Database::query("
		INSERT
		INTO   gl_import_tools (id, group_id, db_class, descr, legacy_keys, modname, sort_order, tool_id, source)
		VALUES (
			{$maintSeq}, 6, 'ImportFrontLinePC', 'FrontLine Position Control', 'SSN,EIN,EFFDATE', 'ImportGeneric&tool_id={$toolID}',
			{$sortOrder}, '{$toolID}', 'NewHires'
		)"
	);
}
else
	$toolID = $res[0]["TOOL_ID"];

$fields = [
	"ssn"                   => [ "type" => "VARCHAR", "length" => "1000", "index" => true ],
	"ein"                   => [ "type" => "VARCHAR", "length" => "1000", "index" => true ],
	"title"                 => [ "type" => "VARCHAR", "length" => "1000", "index" => true ],
	"position"              => [ "type" => "VARCHAR", "length" => "1000", "index" => true ],
	"slot"                  => [ "type" => "VARCHAR", "length" => "1000", "index" => true ],
	"hours"                 => [ "type" => "NUMERIC", "length" => "16,5", "index" => false ],
	"effdate"               => [ "type" => "DATE",    "length" => null,   "index" => false ],
	"fyear"                 => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"staff_id"              => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"position_id"           => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"staff_job_id"          => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"staff_job_position_id" => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"job_id"                => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"slot_id"               => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"slot_effective_id"     => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"slot_pay_id"           => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"slot_pay_effective_id" => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"has_job"               => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"has_position"          => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"has_allocation"        => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"has_salary"            => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"has_deduction"         => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"staff_deduction_id"    => [ "type" => "VARCHAR", "length" => "1000", "index" => false ],
	"slot_pay_effective_id" => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"slot_pay_effective_id" => [ "type" => "BIGINT",  "length" => null,   "index" => true ],
	"row_id"                => [ "type" => "BIGINT",  "length" => null,   "index" => true ]
];

if (!Database::tableExists("gl_frontlinepc_imported"))
{
	$sql = "CREATE TABLE gl_frontlinepc_imported (";

	foreach ($fields as $k => $v)
	{
		if ($v["length"] == null)
			$sql .= "{$k} {$v["type"]}, ";
		else
			$sql .= "{$k} {$v["type"]}({$v["length"]}), ";
	}

	Database::query($sql . "PRIMARY KEY (row_id))");

	foreach ($fields as $k => $v)
		Database::query("CREATE INDEX gl_frontlinepc_imported_{$k} ON gl_frontlinepc_imported ({$k})");
}

foreach ($fields as $k => $v)
{
	if (!Database::columnExists("gl_frontlinepc_imported", $k))
	{
		Database::createColumn("gl_frontlinepc_imported", $k, $v["TYPE"], $v["length"]);

		if ($v["index"])
			Database::query("CREATE INDEX gl_frontlinepc_imported_{$k} ON gl_frontlinepc_imported ({$k})");
	}
}

$template = [
	"5"  => "SSN",
	"76" => "POSITION",
	"77" => "SLOT",
	"79" => "HOURS",
	"80" => "FYEAR",
	"81" => "EFFDATE"
];

foreach ($template as $column => $field)
{
	if (!Database::getIdentityColumn("csv_import_templates")) {
		$res = Database::query("
			INSERT
			INTO   csv_import_templates (id, item_id, column_number, mapped_fields, modname, title)
			SELECT {$maintSeq}, '{$toolID}', {$column}, '{$field}', CONCAT('gl_import_tools_', CAST(i.id AS VARCHAR)), 'FrontLine'
			FROM   gl_import_tools i
			WHERE  i.tool_id = {$toolID}
			AND    NOT EXISTS (SELECT 'x' FROM csv_import_templates t WHERE t.title = 'FrontLine' AND t.item_id = '{$toolID}' AND t.column_number = {$column})"
		);
	} else {
		$res = Database::query("
			INSERT
			INTO   csv_import_templates (item_id, column_number, mapped_fields, modname, title)
			SELECT '{$toolID}', {$column}, '{$field}', CONCAT('gl_import_tools_', CAST(i.id AS VARCHAR)), 'FrontLine'
			FROM   gl_import_tools i
			WHERE  i.tool_id = {$toolID}
			AND    NOT EXISTS (SELECT 'x' FROM csv_import_templates t WHERE t.title = 'FrontLine' AND t.item_id = '{$toolID}' AND t.column_number = {$column})"
		);
	}
}

Database::commit();
