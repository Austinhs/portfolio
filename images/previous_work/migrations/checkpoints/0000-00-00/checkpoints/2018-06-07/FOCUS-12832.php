<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_ap_request", "requesting_facility_id")) {
	Database::createColumn("gl_ap_request", "requesting_facility_id", "BIGINT");
}

Database::commit();
return true;
?>