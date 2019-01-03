<?php

// Change 'tasks.username' to 'tasks.user_class' and 'tasks.user_id'
if(!Database::columnExists('tasks', 'user_class')) {
	Database::createColumn('tasks', 'user_class', 'varchar', 255);
}

if(!Database::columnExists('tasks', 'user_id')) {
	Database::createColumn('tasks', 'user_id', 'bigint');
}

if(Database::columnExists('tasks', 'username')) {
	Database::query("
		UPDATE
			tasks
		SET
			user_class = 'FocusUser',
			user_id = u.staff_id
		FROM
			users u
		WHERE
			u.username = tasks.username
	");

	Database::dropColumn('tasks', 'username');
}

// Add "end_time" and "svn_revision" to updater_log
if(!Database::columnExists('updater_log', 'end_time')) {
	Database::createColumn('updater_log', 'end_time', 'timestamp');
}

if(!Database::columnExists('updater_log', 'svn_revision')) {
	Database::createColumn('updater_log', 'svn_revision', 'bigint');
}

// Migrate the 'SystemUpdateAccess' permission
$update_sql = Database::preprocess("
	INSERT INTO permission (
		id,
		profile_id,
		\"key\"
	)
	SELECT
		{{next:permission_seq}},
		profile_id,
		'School_Setup/SiteAdministration.php:can_edit'
	FROM
		permission
	WHERE
		\"key\" = 'SystemUpdateAccess' AND
		NOT EXISTS(
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = permission.profile_id AND
				p2.\"key\" = 'School_Setup/SiteAdministration.php:can_edit'
		)
");

Database::query($update_sql);
