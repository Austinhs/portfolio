<?php
// FOCUS-18272 - Create a User Calendar
// This migration will mainly update old calendar tables + add new tables
// It will also automatically create a parent/teacher conference category

$effective = DBDate();

$teachers_RET = Database::get(
	"SELECT
		u.STAFF_ID,
		u.LAST_NAME,
		u.FIRST_NAME,
		u.MIDDLE_NAME,
		u.CUSTOM_100000003
	FROM USERS u WHERE EXISTS (
		SELECT ''
		FROM user_enrollment ue
		WHERE u.staff_id = ue.staff_id
		AND EXISTS (
			SELECT '' FROM user_profiles up
			WHERE up.profile = 'teacher'
			AND ue.profiles LIKE concat(',',cast(up.id as varchar),',')
			AND '{$effective}' BETWEEN
				COALESCE(ue.start_date,'{$effective}')
					AND
				COALESCE(ue.end_date,'{$effective}')
		)
	)
	ORDER BY LAST_NAME, FIRST_NAME"
);


if($teachers_RET) {
	$insert = [];
	foreach($teachers_RET as $teacher) {
		$insert[] = [
			'source_id'      => $teacher['STAFF_ID'],
			'source_class'   => 'SISUser',
			'title'          => 'Parent/Teacher Conferences',
			'color'          => '#00FF00',
			'allow_requests' => 'Y'
		];
	}

	CalendarEventCategories::massInsert($insert);
}
