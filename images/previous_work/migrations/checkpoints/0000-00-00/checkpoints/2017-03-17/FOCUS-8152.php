<?php

if($GLOBALS['FocusFinanceConfig']['enabled']) {


	$is_seven = Database::columnExists('permission', 'value');
	$value    = $is_seven ? ' AND VALUE = 1 ' : '';


	$where = "
		SELECT
			DISTINCT p.PROFILE_ID
		FROM
			" . Permission::$table  ." p
		WHERE
			\"key\" = 'menu::gl_ia_manual_journals'
			{$value}
	";

	$profiles = Profile::getAllAndLoad("id in ({$where})");

	$permissions = array(
		[
			'internal' => 'gl::ia_view_all_mj_drafts',
			'district' => 'gl::view_all_mj_drafts'
		],
		[
			'internal' => 'gl::ia_edit_requests',
			'district' => 'gl::edit_requests'
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
					echo "Migration 8152 ran into error when trying to apply new permissions for {$profile_id}";
					// Do nothing further
				}
				Database::commit();
			}
		}
	}
}

?>
