<?php

if(!Database::columnExists("university_courses", "state")) {
	Database::createColumn("university_courses", "state", "VARCHAR");

	Database::query("
		UPDATE
			university_courses
		SET
			state = 'Florida'
		WHERE
			title
		IN (
			'WDIS Subject and Course Setup',
			'WDIS Walk-In Scheduling',
			'Survey 2 Prep',
			'Performance Reports',
			'Process Assessments',
			'Hourly Attendance'

			)
		");
}