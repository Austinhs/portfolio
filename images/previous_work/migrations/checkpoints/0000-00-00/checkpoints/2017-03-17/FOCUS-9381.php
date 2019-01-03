<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$key      = "menu::gl_bank_accounts";
$newKey   = "setup::manage_bank_accounts";
$existing = [];
$hasValue = Database::columnExists(Permission::$table, "value");
$results  = Database::get(
	"SELECT
		DISTINCT profile_id
	FROM
		" . Permission::$table . "
	WHERE
		\"key\" = '{$key}'"
);
$results2 = Database::get(
	"SELECT
		DISTINCT profile_id
	FROM
		" . Permission::$table . "
	WHERE
		\"key\" = '{$newKey}'"
);

foreach ($results2 as $result) {
	$profileId            = $result["PROFILE_ID"];
	$existing[$profileId] = $profileId;
}

Database::begin();

foreach ($results as $result) {
	$profileId = $result["PROFILE_ID"];

	if (isset($existing[$profileId])) {
		continue;
	}

	$permission = new Permission();

	$permission
		->setProfileId($profileId)
		->setKey($newKey);

	if ($hasValue) {
		$permission->setValue(1);
	}

	$permission->persist();
}

Database::commit();

return true;
?>