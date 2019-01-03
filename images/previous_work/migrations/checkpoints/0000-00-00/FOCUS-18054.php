<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

Database::begin();

$timestamp = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";

if (!Database::columnExists("gl_ap_request", "subtype")) {
	Database::createColumn("gl_ap_request", "subtype", "VARCHAR", 1);
}

if (!Database::columnExists("gl_ap_request", "subtype_id")) {
	Database::createColumn("gl_ap_request", "subtype_id", "BIGINT");
}

if (!Database::tableExists("gl_ap_request_subtype")) {
	$sql =
		"CREATE TABLE gl_ap_request_subtype (
			id BIGINT PRIMARY KEY,
			deleted INT,
			type VARCHAR(255),
			available_types TEXT,
			profile_ids TEXT,
			require_receiving INT,
			application_level VARCHAR(1),
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

Database::commit();
return true;
?>