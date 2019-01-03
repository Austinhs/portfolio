<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_pr_history_fica_wage_overrides")) {
	$sql =
		"CREATE TABLE gl_pr_history_fica_wage_overrides (
			id BIGINT PRIMARY KEY,
			deleted INT,
			staff_id BIGINT,
			run_id BIGINT,
			wages numeric
		)";

	Database::query($sql);
}

Database::commit();
return true;
