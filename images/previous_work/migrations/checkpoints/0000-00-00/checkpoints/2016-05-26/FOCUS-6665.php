<?php

$updates = [
	// Update Discipline
	[ 'id' => '239', 'title' => 'Discipline' ],
		//Update LMS Assessment
	[ 'id' => '210', 'title' => 'LMS Assessment'],
		//Update LMS Lesson Planning
	[ 'id' => '212', 'title' => 'LMS Lesson Planning'],
		//Update Teacher Attendance
	[ 'id' => '242', 'title' => 'Teacher Attendance'],
		//Update Teacher Core
	[ 'id' => '240', 'title' => 'Teacher Core' ]
];



foreach($updates as $update) {
	$sql = "
		UPDATE
			university_courses
		SET
			link = :link
		WHERE
			title = :title AND
			NOT EXISTS (
				SELECT
					1
				FROM
					university_courses uc2
				WHERE
					uc2.title = :title AND
					uc2.link = :link AND
					uc2.profiles = university_courses.profiles
			)
	";

	$update['link'] = "https://training.focusschoolsoftware.com/moodle/course/view.php?id={$update['id']}";

	Database::query($sql, $update);
}

$inserts = [
	[
		//Insert LMS Assignments
		'id'       => '211',
		'title'    => 'LMS Assignments',
		'modname'  => 'Grades/Grades.php',
		'profiles' => 'teacher'
	],
	[
		//Insert Non-Instruction Core 8.0
		'id'       => '228',
		'title'    => 'Non-Instructional Core 8.0',
		'modname'  => 'misc/Portal.php',
		'profiles' => 'admin'
	],
	[
		//Insert Teacher Advanced Reports
		'id'       => '241',
		'title'    => 'Teacher Advanced Reports',
		'modname'  => 'misc/Export.php',
		'profiles' => 'teacher'
	],
	//ERP Courses
	[
		//Insert Purchase Order / Request Process
		'id'       => '193',
		'title'    => 'Purchase Order / Request Process',
		'modname'  => 'menu::ap_requests',
		'profiles' => 'ERP'
	],
	[
		//Insert Invoicing
		'id'       => '192',
		'title'    => 'Invoicing',
		'modname'  => 'menu::ap_invoices',
		'profiles' => 'ERP'
	],
	[
		//Insert Checks
		'id'       => '191',
		'title'    => 'Checks',
		'modname'  => 'menu::ap_checks',
		'profiles' => 'ERP'
	],
	[
		//Insert Budgeting
		'id'       => '189',
		'title'    => 'Budgeting',
		'modname'  => 'menu::gl_budget_maintenance',
		'profiles' => 'ERP'
	]
];





foreach($inserts as $insert) {

	$insert['link'] = "https://training.focusschoolsoftware.com/moodle/course/view.php?id={$insert['id']}";

	//check if record exists
	$searchQuery = "
		SELECT '' FROM university_courses
		WHERE
			link     = '{$insert['link']}' AND
			title    = '{$insert['title']}' AND
			modname  = '{$insert['modname']}' AND
			profiles = '{$insert['profiles']}'

		";

	$result = DBGet(DBQuery($searchQuery));

	if(count($result) == 0)
	{
		$sql = Database::preprocess("
			INSERT INTO university_courses (
				{{postgres:id,}}
				link,
				title,
				modname,
				profiles
			)
			VALUES (
				{{postgres:{{next:university_courses_seq}},}}
				:link,
				:title,
				:modname,
				:profiles
			)
		");

		Database::query($sql, $insert);
	}
}


