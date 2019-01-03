<?php
// Populates all gradebooks for a specific year from applicable gradebook templates.

header("content-type: text/plain");

// uncomment for CLI support
// $GLOBALS['disable_login_redirect'] = true;

require_once '../Warehouse.php';
require_once '../modules/Grades/Components/GUser.php';
require_once '../modules/Grades/Components/Grade.php';
require_once '../modules/Grades/Components/GradebookTemplateInit.php';
require_once '../modules/Grades/Components/DB.php';

$syear = 2015; // change this to your desired year

function processSection($syear, $section) {
	global $_FOCUS;
	static $quarters = [];

	$school_id              = $section['SCHOOL_ID'];
	$_REQUEST['school_id']	= $school_id;
	$_SESSION['UserSchool'] = $_REQUEST['school_id'];
	$teacher_id             = $section['TEACHER_ID'];

	$_FOCUS['User'][1] = [
		'STAFF_ID'  => $teacher_id,
		'NAME'      => GetTeacher($section['TEACHER_ID']),
		'USERNAME'  => str_replace("'","''",GetTeacher($teacher_id,'','USERNAME')),
		'PROFILE'   => 'teacher',
		'SCHOOL_ID' => $_SESSION['UserSchool']
	];

	$_SESSION['UserPeriod']       = $section['PERIOD_ID'];
	$_SESSION['UserCoursePeriod'] = $section['COURSE_PERIOD_ID'];

	if (!isset($quarters[$school_id])) {
		$quarters[$school_id] = GetQuarters($school_id, $syear);
	}

	foreach ($quarters[$school_id] as $quarter) {
		$_SESSION['UserMP'] = $quarter['MARKING_PERIOD_ID'];

		$template		= null;
		$template_id	= null;

		InitTemplateForCoursePeriod($template, $template_id);
	}
}

function main() {
	global $ModuleDBErrorHandler, $syear;

	if (User('USERNAME') !== 'focus') {
		die('Error');
	}

	$ModuleDBErrorHandler = function ($sql, $error) {
		echo "\nSQL Error: {$error}\n";
		echo "\nSQL: {$sql}\n";

		die();
	};

	$sql = "
		SELECT
			cp.period_id,
			cp.course_period_id,
			cp.school_id,
			cp.syear,
			cp.teacher_id
		FROM
			schools AS sch
			JOIN course_periods AS cp
				ON (sch.id=cp.school_id)
		WHERE
			cp.syear={$syear}
	";

	$sections = DBGet(DBQuery($sql));

	echo "Found " . count($sections) . " section(s).\n";

	$last_index = count($sections) - 1;

	$prev_session = $_SESSION;

	try {

		foreach ($sections as $index => $section) {
			processSection($syear, $section);

			if (($index + 1) % 100 === 0 || $last_index === $index) {
				echo "Completed " . ($index + 1) . "/" . count($sections) . "\n";
			}
		}

	}
	catch (Exception $e) {
		echo "Exception: " . $e->__toString() . "\n";
	}

	$_SESSION = $prev_session;

	echo "Done.\n";
}

main();
