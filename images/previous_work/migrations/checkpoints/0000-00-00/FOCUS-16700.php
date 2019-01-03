<?php

$profile_permission_seq = Permission::$sequence;
$user_permission_seq    = UserPermission::$sequence;

Database::query(Database::preprocess("
	INSERT INTO permission (id, profile_id, \"key\")
	SELECT
		{{next:{$profile_permission_seq}}},
		profile_id,
		'SIS:DeleteReferrals'
	FROM
		permission p
	WHERE
		\"key\" = 'Discipline/Referrals.php:can_edit'
		AND NOT EXISTS (
			SELECT 1 FROM permission wp WHERE wp.profile_id = p.profile_id AND wp.\"key\" = 'SIS:DeleteReferrals'
		)
"));

Database::query(Database::preprocess("
	INSERT INTO user_permission (id, user_id, \"key\")
	SELECT
		{{next:{$user_permission_seq}}},
		user_id,
		'SIS:DeleteReferrals'
	FROM
		user_permission p
	WHERE
		\"key\" = 'Discipline/Referrals.php:can_edit'
		AND NOT EXISTS (
			SELECT 1 FROM user_permission wp WHERE wp.user_id = p.user_id AND wp.\"key\" = 'SIS:DeleteReferrals'
		)
"));

ProfilePermissions::clearCache();
