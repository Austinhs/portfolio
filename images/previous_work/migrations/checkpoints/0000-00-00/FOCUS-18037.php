<?php
//This migration is a fix for FOCUS-16745 but fixs the keys to be the correct keys & also the broken code
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

//Removing Purchase Requests/Orders -> Internal Purchase Requests/Orders from Migration FOCUS-16745, as this is suppose to fix ess perms
$checks = [
	//ERP
	[ "menu::ess_pay_history",         "hr::view_ess_pay_stub" ],
	[ "menu::ess_employee_tax_forms",  "hr::view_ess_tax_forms"],

	//SIS
	[ "EmployeeSelfService/PayHistory.php:can_view",    "sis::view_ess_pay_stub"],
	[ "EmployeeSelfService/PrintTaxForms.php:can_view", "sis::view_ess_tax_forms"]
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
		foreach($profile_list as $profile_id) {
			foreach($c as $key_check) {
				if(keyDoesNotExist($profile_id, $key_check)) {
					insertKey($profile_id, $key_check);
				}
			}
		}
	}
}

return true;

function keyDoesNotExist($profile_id, $key) {
	if (Database::$type == "postgres") {
		$bool = Database::get("SELECT * FROM permission WHERE \"key\" = '{$key}' AND profile_id = {$profile_id}");
	} else {
		$bool = Database::get("SELECT * FROM permission WHERE [key] = '{$key}' AND profile_id = {$profile_id}");
	}

	//If there is data then do not create key
	if(!empty($bool)) {
		return false;
	} else { // If there is no data then create the key
		return true;
	}
}

function insertKey($profile_id, $key) {
	$next = Database::nextSql("permission_seq");

	if (Database::$type == "postgres") {
		Database::query("
			INSERT
			INTO   permission (\"id\", \"key\", \"profile_id\")
			VALUES ({$next}, '{$key}', {$profile_id})
		");
	} else {
		Database::query("
			INSERT
			INTO   permission ([id], [key], [profile_id])
			VALUES ({$next}, '{$key}', {$profile_id})
		");
	}
}
