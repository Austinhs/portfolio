<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$has_value = Database::columnExists('permission', 'value');

$where = [
	"\"key\" = 'ap_requests'"
];

if($has_value) {
	$where[] = "value = 1";
}

$newKeys = [
	"ap::po_type_standard",
	"ap::po_type_blanket",
	"ap::po_type_warehouse",
	"ap::po_type_stock_warehouse",
	"ap::po_type_utility",
	"ap::po_type_pcard",
	"ap::po_type_voucher"
];

$profile_ids_query = "
	SELECT
		profile_id as ID
	FROM
		permission
	WHERE
		((" . join(') AND (', $where) . "))
";

$profile_ids = Database::get($profile_ids_query);

foreach($profile_ids as $profile) {
	$profile_id = $profile['ID'];

	foreach ($newKeys as $key) {
		$where = array(
			"\"key\"    = '$key'",
			"profile_id = {$profile_id}"
		);

		$tmp_permission = Permission::getOneAndLoad($where);

		if(!empty($tmp_permission)) {
			continue;
		}

		$permission = (new Permission)
			->setKey($key)
			->setProfileId($profile_id);

		if($has_value) {
			$permission->setValue(1);
		}

		$permission->persist();
	}
}
