<?php

Migrations::depend('FOCUS-13326');

if(!Database::columnExists('user_enrollment', 'erp_profiles')) {
	Database::createColumn('user_enrollment', 'erp_profiles', 'text');
}

if(Database::tableExists('user_enrollment') && Database::tableExists('gl_user_profile')) {
	$query = "
		INSERT INTO user_enrollment (staff_id, erp_profiles)
		SELECT
			user_id,
			profile_id
		FROM
			gl_user_profile
		WHERE
			deleted IS NULL
			AND NOT EXISTS(
				SELECT
					1
				FROM
					user_enrollment
				WHERE
					staff_id = user_id
					AND erp_profiles = CAST(profile_id AS VARCHAR)
			)
	";

	Database::query($query);
}

if(Database::tableExists('permission') && Database::tableExists('gl_user_profile')) {
	$keys = [
		[
			'key'          => 'SISUser:enrollment|erp_profiles:can_view',
			'previous_key' => 'SISUser:users|erp_profile_ids:can_view',
		],
		[
			'key'          => 'SISUser:enrollment|erp_profiles:can_edit',
			'previous_key' => 'SISUser:users|erp_profile_ids:can_edit'
		],
	];

	$query = "
		INSERT INTO permission(profile_id, \"key\")
		SELECT
			profile_id, :key AS \"key\"
		FROM
			permission p1
		WHERE
			\"key\" = :previous_key
			AND NOT EXISTS (
				SELECT
					1
				FROM
					permission p2
				WHERE
					p1.profile_id = p2.profile_id
					AND \"key\" != :key
			)
	";

	foreach($keys as $key) {
		Database::query($query, $key);
	}
}
