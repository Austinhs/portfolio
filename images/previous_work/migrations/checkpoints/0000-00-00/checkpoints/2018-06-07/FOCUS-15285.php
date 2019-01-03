<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_fa_request", "condition")) {
	Database::createColumn("gl_fa_request", "condition", "VARCHAR", "255");
}

if (!Database::columnExists("gl_fa_request", "pickup")) {
	Database::createColumn("gl_fa_request", "pickup", "INT");
}

if (!Database::columnExists("gl_fa_request", "reason")) {
	Database::createColumn("gl_fa_request", "reason", "TEXT");
}

if (!Database::columnExists("gl_fa_request", "disposal")) {
	Database::createColumn("gl_fa_request", "disposal", "INT");
}

if (!Database::columnExists("gl_fa_request", "approved_date")) {
	Database::createColumn("gl_fa_request", "approved_date", "TIMESTAMP");
}

Database::commit();
return true;
?>