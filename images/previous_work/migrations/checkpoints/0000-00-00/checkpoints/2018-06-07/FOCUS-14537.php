<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_facilities", "doe_code")) {
	Database::createColumn("gl_facilities", "doe_code", "varchar");
}

Database::commit();
return true;
?>
