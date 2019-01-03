<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5468');

$new_objects = [];

foreach(['edit', 'view'] as $type) {
	$old_key = "Students/StudentFields.php:can_{$type}";
	$new_key = "Validation/EditRules.php:can_{$type}";

	$sql = "
		SELECT DISTINCT
			p1.profile_id
		FROM
			permission p1
		WHERE
			p1.\"key\" = :old_key AND
			NOT EXISTS(
				SELECT
					1
				FROM
					permission p2
				WHERE
					p2.\"key\" = :new_key AND
					p2.profile_id = p1.profile_id
			)
	";

	$params = [
		'old_key' => $old_key,
		'new_key' => $new_key
	];

	$rows = Database::get($sql, $params);

	foreach($rows as $row) {
		$profile_id = intval($row['PROFILE_ID']);

		$object = new Permission();

		$object
			->setProfileId($profile_id)
			->setKey($new_key);

		$new_objects[] = $object;
	}
}

if(!empty($new_objects)) {
	Permission::insert($new_objects);
}
