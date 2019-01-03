<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_budget_scenario_manager", "added_by_manager")) {
	Database::createColumn("gl_budget_scenario_manager", "added_by_manager", "INT");
}

if (!Database::columnExists("gl_budget_scenario_manager_budget", "added_by_manager")) {
	Database::createColumn("gl_budget_scenario_manager_budget", "added_by_manager", "INT");
}

Database::commit();
return true;
?>