<?php

// Migrate new "Edit Linked Users" and "Edit Linked Students" permissions
$linked_students_sql = Database::preprocess("
	INSERT INTO permission (
		id,
		profile_id,
		\"key\"
	)
	SELECT
		{{next:permission_seq}},
		profile_id,
		'SIS:EditStudentLinkedStudents'
	FROM
		permission p1
	WHERE
		\"key\" = 'SIS:EditStudentAddress' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = p1.profile_id AND
				p2.\"key\" = 'SIS:EditStudentLinkedStudents'
		)
");

$linked_users_sql = Database::preprocess("
	INSERT INTO permission (
		id,
		profile_id,
		\"key\"
	)
	SELECT
		{{next:permission_seq}},
		profile_id,
		'SIS:EditStudentLinkedUsers'
	FROM
		permission p1
	WHERE
		\"key\" = 'SIS:EditStudentAddress' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = p1.profile_id AND
				p2.\"key\" = 'SIS:EditStudentLinkedUsers'
		)
");

Database::query($linked_students_sql);
Database::query($linked_users_sql);
