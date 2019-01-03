<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

//JIRA 15503 svn merge svn://focus-sis.com/focus/branches/8.0/dev/FOCUS-15503k -r239848:HEAD
Database::begin();

if (!Database::columnExists("gl_pr_slots", "include_other_supplements_type")) {
	Database::createColumn("gl_pr_slots", "include_other_supplements_type", "char(1)");
}

if (!Database::columnExists("gl_pr_slots", "add_premium_supplements")) {
	Database::createColumn("gl_pr_slots", "add_premium_supplements", "INT");
}

if (!Database::columnExists("gl_pr_slots", "add_standard_supplements")) {
	Database::createColumn("gl_pr_slots", "add_standard_supplements", "INT");
}

if (!Database::columnExists("gl_pr_slots", "calc_percent")) {
	Database::createColumn("gl_pr_slots", "calc_percent", "numeric");
}

Database::commit();
return true;
?>
