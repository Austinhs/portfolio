<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$is_seven = Database::columnExists('permission', 'value');
$value    = $is_seven ? ' AND VALUE = 1 ' : '';


$where = "
	SELECT
		DISTINCT p.PROFILE_ID
	FROM
		" . Permission::$table  ." p
	WHERE
		\"key\" = 'menu::gl_ia_budget_maintenance'
		{$value}
";

$profiles = Profile::getAllAndLoad("id in ({$where})");

$permissions = array(
	[
		'internal' => 'bm::internal_revise_budgets',
		'district' => 'bm::revise_budgets'
	],
	[
		'internal' => 'bm::internal_amend_budgets',
		'district' => 'bm::amend_budgets'
	]
);

foreach($profiles as $profile_id => $profile) {
	if(!$profile_id) {
		continue;
	}

	foreach($permissions as $permission) {
		extract($permission);

		$where = array(
			"profile_id = {$profile_id}",
			"\"key\"    = '{$district}'"
		);

		$profile_permission = Permission::getOneAndLoad($where);

		if(!empty($profile_permission)) {
			$new_where = array(
				"profile_id = {$profile_id}",
				"\"key\"    = '{$internal}'"
			);

			if($is_seven) {
				$new_where[] = 'VALUE = 1';
			}

			$internal_profile_permission = Permission::getOneAndLoad($new_where);

			Database::begin();
			try {
				if(empty($internal_profile_permission)) {
					$new_permission = (new Permission)
						->setKey($internal)
						->setProfileId($profile_id)
						->persist();

					if($is_seven) {
						$new_permission
							->setValue(1)
							->persist();
					}
				} elseif($is_seven) {
					$internal_profile_permission
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

?>
