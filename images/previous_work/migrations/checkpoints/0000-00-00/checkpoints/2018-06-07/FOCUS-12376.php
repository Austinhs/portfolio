<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_fiscal_month", "closed")) {
	Database::createColumn("gl_fiscal_month", "closed", "INT");
}

Database::commit();
return true;
?>