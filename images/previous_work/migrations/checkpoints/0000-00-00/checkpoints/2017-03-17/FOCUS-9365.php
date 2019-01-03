<?php

if(!$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$is_seven    = Database::columnExists('permission', 'value');
$permissions = [
	'ap::ia_reopen_requests' => 'ia_',
	'ap::reopen_requests'    => ''
];

foreach($permissions as $new_key => $prefix) {
	if(!$prefix) {
		$additional = ' OR "key" = \'ap::edit_checks\'';
	}
	else {
		$additional = '';
	}

	$where = [
		'"key" = \'ap::'.$prefix.'close_requests\'' . $additional,
	];

	if($is_seven) {
		$where[] = "value = 1";
	}

	$existing = Permission::getAllAndLoad($where);
	$profiles = [];
	foreach($existing as $id => $old_permission) {
		$profile_id = $old_permission->getProfileId();

		if(!isset($profiles[$profile_id])) {
			$search_where = [
				'"key" = \''.$new_key.'\'',
				"profile_id = {$profile_id}"
			];

			if($is_seven) {
				$search_where[] = 'value = 1';
			}

			$permission_search = Permission::getOne($search_where);

			if(!empty($permission_search)) {
				$profiles[$profile_id] = true;
				continue;
			}

			$new_permission = $old_permission->duplicate();
			$new_permission
				->setKey($new_key)
				->persist();

			$profiles[$profile_id] = true;
		}
	}
}

?>
