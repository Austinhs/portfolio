<?php

include '../Warehouse.php';

if (User('PROFILE') != 'admin') {
	die();
}

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors','On');
include($staticpath.'modules/Scheduling/includes/_makeSectionTitle.fnc.php');

$course_periods_RET = DBGet(DBQuery("SELECT COURSE_PERIOD_ID,TITLE FROM COURSE_PERIODS WHERE SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));

foreach($course_periods_RET as $course_period)
{
	$new_title =_makeSectionTitle(null,$course_period['COURSE_PERIOD_ID']);
	if($new_title!=$course_period['TITLE'])
		DBQuery("UPDATE COURSE_PERIODS SET TITLE='".$new_title."' WHERE COURSE_PERIOD_ID='".$course_period['COURSE_PERIOD_ID']."'");
}

echo '<h3>Done.</h3>';

?>
