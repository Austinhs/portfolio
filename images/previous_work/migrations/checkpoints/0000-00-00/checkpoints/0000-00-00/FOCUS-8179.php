<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$is_seven = Database::columnExists('permission', 'value');
$value    = $is_seven ? ' AND VALUE = 1 ' : '';
$prefix   = [
	'',
	'ia_'
];

foreach($prefix as $pre) {
	$where = "
		SELECT
			DISTINCT p.PROFILE_ID
		FROM
			" . Permission::$table  ." p
		WHERE
			\"key\" = 'bm::{$pre}revise_budgets'
			{$value}
	";

	$profiles = Profile::getAllAndLoad("id in ({$where})");

	$permissions = array(
		"bm::{$pre}new_revision_budgets",
	);

	foreach($profiles as $profile_id => $profile) {
		if(!$profile_id) {
			continue;
		}

		foreach($permissions as $permission) {

			$new_where = array(
				"profile_id = {$profile_id}",
				"\"key\"    = '{$permission}'"
			);

			if($is_seven) {
				$new_where[] = 'VALUE = 1';
			}

			$new_profile_permission = Permission::getOneAndLoad($new_where);

			Database::begin();
			try {
				if(empty($new_profile_permission)) {
					$new_permission = (new Permission)
						->setKey($permission)
						->setProfileId($profile_id)
						->persist();

					if($is_seven) {
						$new_permission
							->setValue(1)
							->persist();
					}
				} elseif($is_seven) {
					$new_profile_permission
						->setValue(1)
						->persist();
				}
			} catch (Exception $e) {
				Database::rollback();
				// Do nothing
			}
			Database::commit();
		}
	}
}
