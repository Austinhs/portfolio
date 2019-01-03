<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$mssql = (Database::$type === "mssql");

if ($mssql) {
	$sql = 
		"DROP INDEX 
			gl_ap_request.gl_ap_request_requisition_number_ind";

	Database::query($sql);
}

Database::changeColumnType("gl_ap_request", "requisition_number", "VARCHAR");

if ($mssql) {
	$sql = 
		"CREATE INDEX 
			gl_ap_request_requisition_number_ind 
		ON 
			gl_ap_request(requisition_number)";

	Database::query($sql);
}

Database::commit();
?>