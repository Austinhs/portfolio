<?php

if(Database::tableExists('__migrate_cf_profiles__')) {
	$sql = "
		SELECT
			*
		FROM
			__migrate_cf_profiles__
	";

	$rows        = Database::get($sql);
	$profiles    = Profile::getAllAndLoad();
	$profile_ids = [];

	foreach($rows as $row) {
		$category_id   = intval($row['CATEGORY_ID']);
		$profile_types = array_flip(array_map('strtolower', json_decode($row['PROFILE_TYPES'], true)));

		foreach($profiles as $profile_id => $profile) {
			$type = strtolower($profile->getProfile());

			if(isset($profile_types[$type])) {
				$profile_ids[$category_id][] = $profile_id;
			}
		}
	}

	foreach($profile_ids as $category_id => $tmp_profile_ids) {
		$category = new CustomFieldCategory($category_id);

		$category
			->setProfiles($tmp_profile_ids)
			->persist();
	}

	Database::query("
		DROP TABLE __migrate_cf_profiles__
	");
}
