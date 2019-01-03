<?php
$changes = [
	'Users/Letters.php:can_view'         => 'FocusUser:EmailLetters',
	'Users/Letters.php:can_edit'         => 'FocusUser:EmailLetters',
	'Grades/ReportCards.php:can_view'    => 'SIS:EmailReportCards',
	'Grades/ReportCards.php:can_edit'    => 'SIS:EmailReportCards',
	'Students/PrintLetters.php:can_view' => 'SISStudent:EmailLetters',
	'Students/PrintLetters.php:can_edit' => 'SISStudent:EmailLetters',
	'Students/Letters.php:can_view'      => 'SISStudent:EmailLetters',
	'Students/Letters.php:can_edit'      => 'SISStudent:EmailLetters',
];

$profile_permission_sql = "
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

$user_permission_sql = "
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

Database::begin();

foreach($changes as $old => $new) {
	$params = [
		'old' => $old,
		'new' => $new,
	];

	Database::query($user_permission_sql, $params);
	Database::query($profile_permission_sql, $params);
}

Database::commit();
