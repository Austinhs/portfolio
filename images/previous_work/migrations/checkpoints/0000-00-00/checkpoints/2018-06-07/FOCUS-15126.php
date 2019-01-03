<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_pr_run_control_dockage_owed_adjustments")) {
	$sql =
		"CREATE TABLE gl_pr_run_control_dockage_owed_adjustments (
			id BIGINT PRIMARY KEY,
			deleted int,
			description varchar(255),
			owed_id bigint
		)";

	Database::query($sql);

	Database::createColumn("gl_pr_run_control_dockage_owed_adjustments", "amount", "NUMERIC");
	Database::createColumn("gl_pr_run_control_dockage_owed_adjustments", "created_date", "timestamp");
	Database::createColumn("gl_pr_run_control_dockage_owed_adjustments", "created_by", "bigint");

}

if (!Database::columnExists("gl_pr_run_control_dockage_owed", "run_limit")) {
	Database::createColumn("gl_pr_run_control_dockage_owed", "run_limit", "NUMERIC");
}

Database::commit();

return true;
