<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql = "
	SELECT
		bm_edit.profile_id,
		CASE WHEN bm_edit.\"key\" = 'bm::edit_requests' THEN 'bm::allow_backdated_budgets'
		     ELSE 'bm::internal_allow_backdated_budgets' END permission
	FROM
		permission bm_edit
	WHERE
		bm_edit.\"key\" IN ('bm::edit_requests', 'bm::internal_edit_requests')
		AND NOT EXISTS (
			SELECT
				*
			FROM
				permission bm_backdate
			WHERE
				bm_edit.profile_id = bm_backdate.profile_id
				AND bm_backdate.\"key\" IN ('bm::allow_backdated_budgets', 'bm::internal_allow_backdated_budgets')
		)
";

$res = Database::get($sql);

Database::begin();

foreach($res as $profile) {
	$profile_id = $profile['PROFILE_ID'];
	$permission = $profile['PERMISSION'];

	(new Permission())
		->setProfileId($profile_id)
		->setKey($permission)
		->persist();
}

Database::commit();

return true;
