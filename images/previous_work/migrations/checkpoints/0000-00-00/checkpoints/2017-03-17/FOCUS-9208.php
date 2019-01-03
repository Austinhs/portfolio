<?php

$inserts = [
	[
		//Insert CTE Training (Admin)
		'id'       => '26',
		'title'    => 'CTE Training (Admin)',
		'modname'  => 'misc/Portal.php',
		'profiles' => 'admin'
	],
	[
		//Insert CTE Training (Teacher)
		'id'       => '27', 
		'title'    => 'CTE Training (Teacher)', 
		'modname'  => 'misc/Portal.php', 
		'profiles' => 'teacher'
	]
];


foreach($inserts as $insert) {

	$insert['link'] = "https://training.focusschoolsoftware.com/moodle/course/category.php?id={$insert['id']}";

	//check if record exists
	$searchQuery = "
		SELECT '' FROM university_courses 
		WHERE 
			link     = '{$insert['link']}' AND
			title    = '{$insert['title']}' AND
			modname  = '{$insert['modname']}' AND
			profiles = '{$insert['profiles']}'

		";

	$result = Database::get($searchQuery);

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

	
