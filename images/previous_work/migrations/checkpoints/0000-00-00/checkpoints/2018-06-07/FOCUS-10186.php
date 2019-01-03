<?php
$has_value = Database::columnExists('permission', 'value');

if($has_value){
	Database::query("
		INSERT INTO permission (profile_id, \"key\", value)
		SELECT
			profile_id,
			'SystemUpdateAccess',
			value
		FROM
			permission p1
		WHERE
			\"key\" = 'School_Setup/SystemPreferences.php:can_edit'
			AND NOT EXISTS (
				SELECT
					*
				FROM
					permission
				WHERE
					\"key\" = 'SystemUpdateAccess'
					AND p1.profile_id = profile_id
			)
	");
} else {
	Database::query("
		INSERT INTO permission (profile_id, \"key\")
		SELECT
			profile_id,
			'SystemUpdateAccess'
		FROM
			permission p1
		WHERE
			\"key\" = 'School_Setup/SystemPreferences.php:can_edit'
			AND NOT EXISTS (
				SELECT
					*
				FROM
					permission
				WHERE
					\"key\" = 'SystemUpdateAccess'
					AND p1.profile_id = profile_id
			)
	");
}
