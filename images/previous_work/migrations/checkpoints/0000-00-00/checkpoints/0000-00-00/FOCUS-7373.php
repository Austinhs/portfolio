<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$is_seven        = Database::columnExists('permission', 'value');
$new_permissions = array(
	"ap::edit_payment_method",
	"ap::view_payment_method"
);
$search_query = "
	SELECT DISTINCT
	 	p1.profile_id
	FROM
		" . Permission::$table . " p1
	WHERE
		p1.\"key\" = 'menu::ap_vendors' AND
		NOT EXISTS(
			SELECT
				1
			FROM
				permission p2
			WHERE
				(
					p2.\"key\" = 'ap::edit_payment_method' OR
					p2.\"key\" = 'ap::view_payment_method'
				) AND
				p2.profile_id = p1.profile_id
		)
";

if($is_seven) {
	$search_query .= " AND value = '1'";
}

$profiles = Database::get($search_query);

foreach($profiles as $profile) {
	$profile_id = $profile['PROFILE_ID'];

	if(empty($profile_id)) {
		continue;
	}

	foreach($new_permissions as $new_key) {
		$permission = (new Permission)
			->setProfileId($profile_id)
			->setKey($new_key);

		if($is_seven) {
			$permission->setValue(1);
		}

		$permission->persist();
	}
}
