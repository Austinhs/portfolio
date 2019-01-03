<?php

$keys = [
	["key" => "SIS:EditInactiveStudents"],
	["key" => "SIS:EditPreviousYearsInactiveStudents"],
];

$sql = "
	INSERT INTO
		permission (profile_id, \"key\")
	SELECT
		profile_id, :key AS \"key\"
	FROM
		permission p1
	WHERE
		\"key\" IN ('Students/Student.php:can_view', 'Students/Student.php:can_edit')
		AND NOT EXISTS (
			SELECT
				p2.profile_id
			FROM
				permission p2
			WHERE
				p1.profile_id = p2.profile_id
				AND \"key\" = :key

		)
	GROUP BY
		profile_id
	HAVING
		COUNT(profile_id) > 1;
";

if(Database::tableExists("permission")) {
	foreach($keys as $key) {
		Database::query($sql, $key);
	}
}
