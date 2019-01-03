<?php

// If the user already has the 'SIS:EditStudentAddress' permission, give them the new permission 'SIS:EditStudentPrimaryResidence'
Database::query("
	INSERT INTO permission (
		profile_id,
		\"key\"
	) (
	SELECT
		profile_id,
		'SIS:EditStudentPrimaryResidence'
	FROM
		permission p1
	WHERE
		\"key\" = 'SIS:EditStudentAddress' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission
			WHERE
				\"key\"    = 'SIS:EditStudentPrimaryResidence' AND
				profile_id = p1.profile_id
		)
	)
");

Database::query("
	INSERT INTO user_permission (
		user_id,
		\"key\"
	) (
	SELECT
		user_id,
		'SIS:EditStudentPrimaryResidence'
	FROM
		user_permission p1
	WHERE
		\"key\" = 'SIS:EditStudentAddress' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				user_permission
			WHERE
				\"key\" = 'SIS:EditStudentPrimaryResidence' AND
				user_id = p1.user_id
		)
	)
");
