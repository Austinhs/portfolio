<?php
Migrations::depend('FOCUS-13326');

if(Database::tableExists('permission')) {
	$keys = [
		[
			'old' => 'FocusUser:users|erp_profile_ids:can_view',
			'new' => 'FocusUser:enrollment|erp_profiles:can_view',
		],
		[
			'old' => 'FocusUser:users|erp_profile_ids:can_edit',
			'new' => 'FocusUser:enrollment|erp_profiles:can_edit',
		],
		[
			'old' => 'FocusUser:users|erp_profile_ids:approval',
			'new' => 'FocusUser:enrollment|erp_profiles:approval',
		],
	];

	$query = "
		UPDATE
			permission
		SET
			\"key\" = :new
		WHERE
			\"key\" = :old AND
			NOT EXISTS(
				SELECT
					1
				FROM
					permission
				WHERE
					\"key\" = :new
			)
	";

	foreach($keys as $key) {
		Database::query($query, $key);
	}
}

