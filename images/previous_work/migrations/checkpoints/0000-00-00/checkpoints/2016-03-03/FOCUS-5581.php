<?php

Migrations::depend('FOCUS-6359');

if(!empty($GLOBALS['_FOCUS']['config']['state_name'])) {
	$state_name = trim(strtolower($GLOBALS['_FOCUS']['config']['state_name']));

	if($state_name === 'florida') {
		if(!Database::columnExists('student_enrollment', 'came_from_school')) {
			Database::createColumn('student_enrollment', 'came_from_school', 'varchar');
		}

		if(!Database::columnExists('student_enrollment', 'went_to_school')) {
			Database::createColumn('student_enrollment', 'went_to_school', 'varchar');
		}
	}
}
