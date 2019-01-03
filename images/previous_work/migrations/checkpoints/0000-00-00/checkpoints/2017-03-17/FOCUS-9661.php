<?php

if(!$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$types = [
	'menu::ap_invoices'          => 'ap::view_all_batches',
	'menu::ap_internal_invoices' => 'ap:ia_view_all_batches'
];

foreach($types as $menu_key => $permission_key) {
	$where = [
		"\"key\" = '{$menu_key}'",
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
