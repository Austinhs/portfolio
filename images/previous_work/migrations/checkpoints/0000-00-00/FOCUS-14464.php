<?php

$db_type = Database::$type;

if ($db_type === "mssql") {
	Database::query("
		UPDATE
			schedule
		SET
			reauthorized = '1'
		FROM
			schedule s2
		WHERE
			s2.student_id = student_id
			AND s2.school_id = school_id
			AND s2.syear = syear
			AND reauthorized IS NULL
			AND CONCAT(
				',', s2.reauthorization_schedule_id,
				','
			) LIKE CONCAT('%,', id, ',%')
	");
} else if ($db_type === "postgres") {
	Database::query("
		UPDATE
			schedule
		SET
			reauthorized = '1'
		FROM 
			schedule s2
		WHERE
			s2.student_id = schedule.student_id
			AND s2.school_id = schedule.school_id
			AND s2.syear = schedule.syear
			AND schedule.reauthorized IS NULL
			AND CONCAT(
				',', s2.reauthorization_schedule_id,
				','
			) LIKE CONCAT('%,', schedule.id, ',%')
	");
}

