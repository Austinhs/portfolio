<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_pos_outside_disbursement")) {
	$timestamp = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";
	$sql       =
		"CREATE TABLE gl_pos_outside_disbursement (
			id BIGINT PRIMARY KEY,
			deleted INT,
			customer_id BIGINT,
			funding_source_id BIGINT,
			amount NUMERIC(28,10),
			date {$timestamp}
		)";

	Database::query($sql);
}

Database::commit();
return true;
?>