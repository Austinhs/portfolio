<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_pay_types", "summer_school")) {
	Database::createColumn("gl_pr_pay_types", "summer_school", "int");
}

Database::commit();
return true;
?>
