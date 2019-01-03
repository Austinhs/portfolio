<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_benefit_codes", "deleted")) {
	Database::createColumn("gl_benefit_codes", "deleted", "int");
}

return true;
