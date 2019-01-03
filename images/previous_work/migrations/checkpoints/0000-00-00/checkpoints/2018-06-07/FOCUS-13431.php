<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

$focus_user = Database::get("SELECT staff_id FROM users WHERE LOWER(username)='focus'");
$focus_user = array_column($focus_user, 'STAFF_ID');
$focus_user = reset($focus_user);
$nullable   = !empty($focus_user);

if (!Database::columnExists('sss_caseload', 'staff_id')) {
	Database::createColumn('sss_caseload', 'staff_id', 'numeric', '', $nullable);
}

if ($nullable) {
	Database::query("UPDATE sss_caseload SET staff_id = {$focus_user}");
	$set = Database::$type === "postgres" ? "SET" : "NUMERIC";
	Database::query("ALTER TABLE sss_caseload ALTER COLUMN staff_id {$set} NOT NULL");
}
