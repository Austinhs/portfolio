<?php
$teacher_sql = "
	SELECT
		id
	FROM
		user_profiles
	WHERE
		profile = 'teacher'
";

$teacher_result   = Database::get($teacher_sql);
$teacher_profiles = array_column($teacher_result, 'ID');
$teacher_profiles = implode(',', $teacher_profiles);

$profile_permission_sql = "
	INSERT INTO permission (
		profile_id,
		\"key\"
	)
	SELECT
		profile_id,
		:new
	FROM
		permission p
	WHERE
		\"key\" = :old AND
		p.profile_id in ({$teacher_profiles}) AND
		NOT EXISTS(
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = p.profile_id AND
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
		user_permission up
	JOIN
		users u ON
		u.staff_id = up.user_id AND
		u.profile = 'teacher'
	WHERE
		up.\"key\" = :old AND
		NOT EXISTS(
			SELECT
				1
			FROM
				user_permission up2
			WHERE
				up2.user_id = up.user_id AND
				up2.\"key\" = :new
		)
";

$permission_params = [
	'old' => 'Discipline/Referrals.php:can_view',
	'new' => 'SIS:ViewOtherReferrals',
];

Database::query($profile_permission_sql, $permission_params);
Database::query($user_permission_sql, $permission_params);
