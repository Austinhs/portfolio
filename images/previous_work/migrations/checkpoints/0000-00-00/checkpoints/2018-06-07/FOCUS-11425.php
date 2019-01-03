<?php

Database::query("
	INSERT INTO permission (profile_id, \"key\")
	SELECT
		profile_id,
		'SIS:AllowOverfillSections'
	FROM
		permission p1
	WHERE
		\"key\" = 'SIS:SchedulingEditSections' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission
			WHERE
				\"key\"    = 'SIS:AllowOverfillSections' AND
				profile_id = p1.profile_id
		)
");

Database::query("
	INSERT INTO user_permission (user_id, \"key\")
	SELECT
		user_id,
		'SIS:AllowOverfillSections'
	FROM
		user_permission p1
	WHERE
		\"key\" = 'SIS:SchedulingEditSections' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				user_permission
			WHERE
				\"key\" = 'SIS:AllowOverfillSections' AND
				user_id = p1.user_id
		)
");
