<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql    =
	"SELECT
		profile_id,
		\"key\" AS permission
	FROM
		permission
	WHERE
		\"key\" IN ('ar::create_receipts', 'ar::post_receipts', 'ar::edit_receipts', 'ar::delete_receipts')";
$res    = Database::get($sql);
$insert = [];

foreach ($res as $data) {
	$profile_id = $data["PROFILE_ID"];
	$permission = str_ireplace("ar::", "ar::ia_", $data["PERMISSION"]);

	$insert[] = [
		"profile_id" => $profile_id,
		"key"        => $permission
	];
}

if ($insert) {
	Database::insert(Permission::$table, Permission::$sequence, array_keys($insert[0]), $insert);
}

Database::commit();
return true;
?>