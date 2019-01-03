<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_wh_maintenance_request_items")) {
	return false;
}

if (!Database::tableExists("gl_wh_technician")) {
	Database::begin();

	$isPostgres = Database::$type === "postgres";
	$postgres   = "";
	$mssql      = "";
	if ($isPostgres) {
		$postgres = ", PRIMARY KEY (id)";
	} else {
		$mssql = "NOT NULL PRIMARY KEY";
	}

	$sql =
		"CREATE TABLE gl_wh_technician (
			id BIGINT {$mssql},
			deleted BIGINT,
			name VARCHAR(255)
			{$postgres}
		)";

	Database::query($sql);

	$sql = 
		"UPDATE 
			gl_wh_technician 
		SET 
			id = {{next:gl_maint_seq}}";

	$sql = Database::preprocess($sql);

	Database::query($sql);
	Database::commit();
}

if (!Database::columnExists("gl_wh_maintenance_request_items", "technician_id")) {
	Database::begin();
	Database::createColumn("gl_wh_maintenance_request_items", "technician_id", "BIGINT");
	Database::commit();
}

return true;
?>