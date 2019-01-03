<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$timestamp = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";

if (!Database::tableExists("gl_trial_balance_log")) {
	$sql =
		"CREATE TABLE gl_trial_balance_log (
			id BIGINT PRIMARY KEY,
			created_at {$timestamp},
			result TEXT
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_trial_balance_log_subscription")) {
	$sql =
		"CREATE TABLE gl_trial_balance_log_subscription (
			id BIGINT PRIMARY KEY,
			user_id BIGINT
		)";

	Database::query($sql);
}

Database::commit();
return true;