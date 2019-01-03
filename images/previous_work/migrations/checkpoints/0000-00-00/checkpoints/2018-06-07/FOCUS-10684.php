<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_element_range")) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_element_range", "gl_accounts")) {
	Database::createColumn("gl_element_range", "gl_accounts", "TEXT");
}

if (!Database::columnExists("gl_element_range", "element_range_id")) {
	Database::createColumn("gl_element_range", "element_range_id", "BIGINT");
}

Database::commit();

return true;
?>