<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$pages = array(
	'menu::jobs' => 'hr::view_jobs',
	'menu::deductions' => 'hr::view_deductions',
	'menu::payhistory' => 'hr::payhistory',
	'menu::files' => 'hr::files',
	'menu::retiree_benefits' => 'hr::retiree_benefits',
	'menu::retiree_payments' => 'hr::retiree_payments'
);
foreach ($pages as $old_key => $new_key) {
	Database::query(
		"INSERT INTO PERMISSION (PROFILE_ID,\"key\")
		SELECT
			PROFILE_ID, '{$new_key}' as \"key\"
		FROM PERMISSION p
		WHERE
			\"key\" = '{$old_key}'
			AND NOT EXISTS(
				SELECT ''
				FROM PERMISSION
				WHERE PROFILE_ID = p.PROFILE_ID AND \"key\" = '{$new_key}'
			)"
	);
}
$access_profiles = Database::get(
	"SELECT PROFILE_ID
	FROM PERMISSION
	WHERE \"key\" = 'menu::employee' AND NOT EXISTS(SELECT '' FROM PERMISSION WHERE \"key\"='hr::employee_demographic' AND PROFILE_ID = PERMISSION.PROFILE_ID)"
);
foreach ($access_profiles as $access_profile) {
	Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'hr::employee_demographic')");
}

Database::commit();