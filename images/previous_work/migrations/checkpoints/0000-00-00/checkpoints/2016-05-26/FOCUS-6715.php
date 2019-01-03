<?php

foreach(['Add', 'Drop'] as $type) {
	$sql = "
		UPDATE
			student_enrollment_codes
		SET
			type = :type
		WHERE
			LOWER(type) = LOWER(:type)
	";

	$params = [
		'type' => $type
	];

	Database::query($sql, $params);
}
