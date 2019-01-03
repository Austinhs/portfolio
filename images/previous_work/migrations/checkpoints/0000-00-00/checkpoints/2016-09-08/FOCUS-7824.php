<?php

global $DatabaseType;

$courseData = [
	"Advanced Reports" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=237",
	"Built In Reports" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=134",
	"Course Request and Reports" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=258",
	"Creative Scheduling" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=117",
	"Custom Reports and Dashboards" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=168",
	"Discipline" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=239",
	"Elementary Scheduling - Setup" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=273",
	"Elementary Package Scheduling" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=268",
	"End of Year Process" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=155",
	"Enrollment" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=269",
	"Graduation Requirements and Progression Plans" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=124",
	"Health" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=206",
	"Hourly Attendance" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=95",
	"Importing Test Scores" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=146",
	"Letters and Letterheads" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=199",
	"LMS Assessments" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=210",
	"LMS Assignments" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=211",
	"LMS Lesson Planner" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=212",
	"Manage Integrations" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=144",
	"Mass Updating Student Info and Mass Adding Logging Fields" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=126",
	"Master Schedule Builder" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=123",
	"Middle School Team Scheduling" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=65",
	"Non-Instructional Core 8.0" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=228",
	"Online Enrollment/Re-Enrollment Process" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=138",
	"Performance Reports" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=216",
	"Process Assessments" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=139",
	"Rollover Retention" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=91",
	"Secondary Scheduling - Setup" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=249",
	"Scheduling Walk-In" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=250",
	"Standards Setup and Assignment" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=162",
	"Student Field" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=229",
	"Student Search Menu" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=46",
	"Summer School Scheduling" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=148",
	"Survey 2 Prep" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=185",
	"Teacher Attendance" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=242",
	"Teacher Core" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=240",
	"Team Scheduling" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=248",
	"Transcripts" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=204",
	"Translations" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=197",
	"WDIS Subject and Course Setup" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=129",
	"WDIS Walk-In Scheduling" => "https://training.focusschoolsoftware.com/moodle/course/view.php?id=156"
];

// Remove the existing links for the courses that have updates
$courseTitles = array_keys($courseData);
Database::query("
	DELETE 
		FROM university_courses 
	WHERE 
		profiles = 'admin' 
		AND title IN ('".implode("', '", $courseTitles)."')"
);

foreach ($courseData as $name => $link) {
	$idColumn = "";
	$idValue = "";

	if ($DatabaseType != 'mssql') {
		$idColumn = "id,";
		$idValue = "'".Database::nextValue('university_courses_seq')."',";
	}

	Database::query("
		INSERT INTO university_courses (
			{$idColumn}
			title,
			modname,
			link,
			profiles
		)
		VALUES (
			{$idValue}
			'{$name}',
			'misc/Portal.php',
			'{$link}',
			'admin'
		)
	");
}
