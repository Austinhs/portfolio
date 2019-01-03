<?php


//This delete query will remove any records in students_join_users where
//the user no longer exists. When a user exists in students_join_users, but
//not in users, it throws an error on the Linked Users secion
$sql = "
DELETE FROM
	students_join_users
WHERE
	staff_id IN (
		SELECT
			sju.staff_id
		FROM
			students_join_users sju
			LEFT JOIN users u ON (sju.staff_id = u.staff_id)
		WHERE
			u.staff_id IS NULL
	);";


Database::query($sql);