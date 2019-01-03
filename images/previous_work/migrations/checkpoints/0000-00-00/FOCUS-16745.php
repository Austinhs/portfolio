<?php

//Setting this to return true as its depricated in FOCUS-18037, FOCUS-18037 will do exactly the same thing but fix the migration.
return true;

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$checks = [
	[ "ap_requests",            "ap_internal_requests" ],
	[ "ess_pay_history",        "ess_pay_stub" ],
	[ "ess_employee_tax_forms", "ess_print_tax_forms" ]
];

foreach ($checks as $c) {
	$profile_list = [];

	if (Database::$type == "postgres") {
		$res = Database::get("SELECT DISTINCT \"profile_id\" FROM \"permission\" WHERE \"key\" = '{$c[0]}' OR \"key\" = '{$c[1]}' ORDER BY profile_id");
	} else {
		$res = Database::get("SELECT DISTINCT [profile_id] FROM [permission] WHERE [key] = '{$c[0]}' OR [key] = '{$c[1]}' ORDER BY profile_id");
	}

	foreach ($res as $r) {
		$profile_list[] = $r["PROFILE_ID"];
	}

	if (!empty($profile_list)) {
		$profile_list = implode(", ", $profile_list);
		$next         = Database::nextSql("permission_seq");

		if (Database::type == "postgres") {
			Database::query("
				INSERT
				INTO   permission (\"id\", \"key\", \"profile_id\")
				SELECT {$next}, id, '{$c[0]}'
				FROM   user_profiles
				WHERE  user_profiles.id IN ({$profile_list})
				AND    NOT EXISTS (SELECT 'x' FROM permission p1 WHERE p1.profile_id = user_profiles.id AND p1.key = {$c[0]})"
			);
			Database::query("
				INSERT
				INTO   permission (\"id\", \"key\", \"profile_id\")
				SELECT {$next}, id, '{$c[1]}'
				FROM   user_profiles
				WHERE  user_profiles.id IN ({$profile_list})
				AND    NOT EXISTS (SELECT 'x' FROM permission p1 WHERE p1.profile_id = user_profiles.id AND p1.key = {$c[1]})"
			);
		} else {
			Database::query("
				INSERT
				INTO   permission ([id], [key], [profile_id])
				SELECT {$next}, id, '{$c[0]}'
				FROM   permission p
				JOIN   user_profiles up ON up.id = p.profile_id
				WHERE  up.id IN ({$profile_list})
				AND    NOT EXISTS (SELECT 'x' FROM permission p1 WHERE p1.profile_id = up.id AND p1.key = {$c[0]})"
			);
			Database::query("
				INSERT
				INTO   permission ([id], [key], [profile_id])
				SELECT {$next}, id, '{$c[1]}'
				FROM   permission p
				JOIN   user_profiles up ON up.id = p.profile_id
				WHERE  up.id IN ({$profile_list})
				AND    NOT EXISTS (SELECT 'x' FROM permission p1 WHERE p1.profile_id = up.id AND p1.key = {$c[1]})"
			);
		}
	}
}

return true;
