<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_ap_buyer")) {
	$time = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";
	$sql  = 
		"CREATE TABLE gl_ap_buyer (
			id BIGINT PRIMARY KEY,
			deleted INT,
			code VARCHAR(255),
			title VARCHAR(255),
			user_id BIGINT,
			updated_at {$time}
		)";

	Database::query($sql);
}

if (!Database::columnExists("gl_ap_request", "buyer_id")) {
	Database::createColumn("gl_ap_request", "buyer_id", "BIGINT");
}

Database::commit();
return true;
?>