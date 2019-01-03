<?php

$params = [
	'old' => 'Students/AddStudent.php:can_edit',
	'new' => 'Students/AddStudent.php:can_view'
];

//Delete remenants from old permissions system

$delete_sql = "
	DELETE FROM
		permission
	WHERE
		\"key\" = :new
";
Database::query($delete_sql, $params);

//Update Permission table

$permission_sql = "
	INSERT INTO permission (
		profile_id,
		\"key\"
	)
	SELECT
		profile_id,
		:new
	FROM
		permission
	WHERE
		\"key\" = :old AND
		NOT EXISTS(
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = permission.profile_id AND
				p2.\"key\" = :new
		)
";
Database::query($permission_sql, $params);

// Update User Permission table

$user_permissions_sql = "
	INSERT INTO user_permission (
		user_id,
		\"key\"
	)
	SELECT
		user_id,
		:new
	FROM
		user_permission
	WHERE
		\"key\" = :old AND
		NOT EXISTS(
		SELECT
			1
		FROM
			user_permission up2
		WHERE
			up2.user_id = user_permission.user_id AND
			up2.\"key\" = :new
		)
";
Database::query($user_permissions_sql, $params);