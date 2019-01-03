<?php

// If a user can view Student Info, then add permission to View Second School Enrolled Students
Database::query("
	INSERT INTO permission (profile_id, \"key\")
	SELECT
		profile_id,
		'SIS:ViewSecondSchoolEnrolledStudents'
	FROM
		permission p1
	WHERE
		\"key\" = 'Students/Student.php:can_view' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission
			WHERE
				\"key\"    = 'SIS:ViewSecondSchoolEnrolledStudents' AND
				profile_id = p1.profile_id
		)
");
