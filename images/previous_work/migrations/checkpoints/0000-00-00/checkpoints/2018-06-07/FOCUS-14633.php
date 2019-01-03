<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$access_profiles = Database::get(
	"SELECT p.PROFILE_ID
	FROM PERMISSION p
	WHERE p.\"key\" = 'menu::employee' AND NOT EXISTS(SELECT '' FROM PERMISSION WHERE \"key\"='hr::employee_demographic' AND PROFILE_ID = p.PROFILE_ID)"
);
foreach ($access_profiles as $access_profile) {
	Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'hr::employee_demographic')");
}

Database::commit();