<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if(!Database::columnExists("gl_pr_run_controls", "debug_switch")) {
	Database::createColumn("gl_pr_run_controls", "debug_switch", "integer");
}

if(!Database::columnExists("gl_pr_run_controls", "debug_section")) {
	Database::createColumn("gl_pr_run_controls", "debug_section", "varchar(200)");
}

Database::commit();
