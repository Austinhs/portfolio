<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$nxt = Database::nextSql("gl_maint_seq");
$res = Database::get("SELECT id FROM gl_import_tools WHERE db_class = 'ImportTransTime'");

if (empty($res)) {
	Database::query("
		INSERT
		INTO   gl_import_tools (db_class, descr, group_id, id, modname, sort_order, tool_id)
		VALUES ('ImportTransTime', 'Transportation Time', 6, {$nxt}, 'ImportGeneric&tool_id=620', 20, 620)"
	);
}

$res = Database::get("SELECT id FROM gl_import_tools WHERE db_class = 'ImportTransLeave'");

if (empty($res)) {
	Database::query("
		INSERT
		INTO   gl_import_tools (db_class, descr, group_id, id, modname, sort_order, tool_id)
		VALUES ('ImportTransLeave', 'Transportation Leave', 6, {$nxt}, 'ImportGeneric&tool_id=630', 30, 630)"
	);
}

if (!Database::getIdentityColumn("csv_import_templates")) {
	$res    = Database::get("SELECT MAX(id) AS last_id FROM csv_import_templates");
	$nextID = intval($res[0]["LAST_ID"]) + 1;
	$cur    = Database::currentSql("csv_import_templates_seq");

	if (Database::$type == "postgres") {
		$res = Database::get("SELECT last_value FROM csv_import_templates_seq");
	} else {
		$res = Database::get("SELECT current_value AS last_value FROM sys.sequences WHERE name = 'csv_import_templates_seq'");
	}

	$curSeq = intval($res[0]["LAST_VALUE"]);

	if ($curSeq < $nextID) {
		Database::query("ALTER SEQUENCE csv_import_templates_seq RESTART WITH {$nextID}");
	}
}

$nxt = Database::nextSql("csv_import_templates_seq");
$col = Database::getIdentityColumn("csv_import_templates") ? "" : "id, ";
$val = Database::getIdentityColumn("csv_import_templates") ? "" : "{$nxt}, ";
$res = Database::get("SELECT id FROM csv_import_templates WHERE item_id = 620");

if (empty($res)) {
	Database::query("
		INSERT
		INTO   csv_import_templates ({$col}title, modname, item_id, column_number, mapped_fields, column_length)
		VALUES ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 1, 'EIN', 10),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 11, 'JOB', 2),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 13, 'CNTR', 4),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 17, 'RUN', 3),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 20, 'PPFROM', 8),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 28, 'PPTO', 8),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 36, 'LNAME', 20),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 56, 'FNAME', 20),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 76, 'PAYTYPE', 3),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 79, 'HRLYSAL', 1),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 80, 'REGHRS', 7),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 87, 'OTSTD', 7),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 94, 'OTHALF', 7),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 101, 'FTSTD', 7),
		       ({$val}'TransportationTime', 'gl_import_tools_396641232', 620, 108, 'FTHALF', 7)"
	);
}

$res = Database::get("SELECT id FROM csv_import_templates WHERE item_id = 630");

if (empty($res)) {
	Database::query("
		INSERT
		INTO   csv_import_templates ({$col}title, modname, item_id, column_number, mapped_fields, column_length)
		VALUES ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 1, 'EIN', 10),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 11, 'JOB', 2),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 13, 'CNTR', 4),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 17, 'RUN', 3),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 20, 'PPFROM', 8),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 28, 'PPTO', 8),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 36, 'LVDATE', 8),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 44, 'LVBUCKET', 3),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 47, 'LVREASON', 3),
		       ({$val}'TransportationLeave', 'gl_import_tools_396641249', 630, 50, 'HOURS', 7)"
	);
}

return true;
