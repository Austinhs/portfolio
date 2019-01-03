<?php

if(!$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$types = [];

if(intval(Settings::get("pos_allow_other_payment"))) {
	$types[] = 'ar::pos_other_refund';
}

if(intval(Settings::get("process_credit"))) {
	$types[] = 'ar::process_credit_card';
}

foreach($types as $permission_key) {
	$where = [
		"\"key\" = 'menu::ar_pos'",
		"not exists (
			select
				''
			FROM
				".Permission::$table." p2
			WHERE
				p2.\"key\" = '{$permission_key}'
		)"
	];

	$permissions = Permission::getAllAndLoad($where);

	foreach($permissions as $permission) {
		$new_permission = $permission->duplicate();
		$new_permission
			->setKey($permission_key)
			->persist();
	}
}
