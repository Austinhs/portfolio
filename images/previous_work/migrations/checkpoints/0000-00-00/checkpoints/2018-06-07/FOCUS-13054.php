<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

$interesting_goals = Database::get("SELECT id FROM sss_goals WHERE unknown_column1 IS NOT NULL");
$interesting_goals = array_column($interesting_goals, 'ID');

foreach ($interesting_goals as $goal_id) {
	$measurement_method = Database::get("SELECT measurement_method FROM sss_goals WHERE id = {$goal_id}");
	$measurement_method = json_decode($measurement_method[0]['MEASUREMENT_METHOD'], true);
	$measurement_method[] = "4";
	$measurement_method = DBEscapeString(json_encode($measurement_method));

	Database::query("UPDATE sss_goals SET measurement_method = '{$measurement_method}' WHERE id = {$goal_id}");
}

if(!Database::columnExists('sss_goals', 'measurement_other')) {
	Database::renameColumn('unknown_column1', 'measurement_other', 'sss_goals');
}

if(!Database::columnExists('sss_goals', 'frequency_other')) {
	Database::createColumn('sss_goals', 'frequency_other', 'VARCHAR', 255);
}

if(!Database::columnExists('sss_goals', 'other_responsible_implementers')) {
	Database::createColumn('sss_goals', 'other_responsible_implementers', 'VARCHAR', 255);
}

$interesting_goals = Database::get("SELECT id FROM sss_goals WHERE assessment_procedures LIKE '%\"Test(s)\"%'");
$interesting_goals = array_column($interesting_goals, 'ID');
foreach ($interesting_goals as $goal_id) {
	$procedures = Database::get("SELECT assessment_procedures FROM sss_goals WHERE id = {$goal_id}");
	$procedures = json_decode($procedures[0]['ASSESSMENT_PROCEDURES'], true);

	$index = array_search('Test(s)', $procedures);
	if ($index !== false) {
		array_splice($procedures, $index, 1, 'Assessment(s)');
		$procedures = DBEscapeString(json_encode($procedures));
		Database::query("UPDATE sss_goals SET assessment_procedures = '{$procedures}' WHERE id = {$goal_id}");
	}
}

$interesting_goals = Database::get("SELECT id FROM sss_goals WHERE responsible_implementer LIKE '%AI%' OR responsible_implementer LIKE '%VI%' OR responsible_implementer LIKE '%LSSP%'");
$interesting_goals = array_column($interesting_goals, 'ID');
$replacer = function(&$arr, $old_option, $new_option) {
	$index = array_search($old_option, $arr);
	if ($index !== false) {
		array_splice($arr, $index, 1, $new_option);
	}
};


foreach ($interesting_goals as $goal_id) {
	$implementers = Database::get("SELECT responsible_implementer FROM sss_goals WHERE id = {$goal_id}");
	$implementers = json_decode($implementers[0]['RESPONSIBLE_IMPLEMENTER'], true);

	$replacer($implementers, 'VI Teacher', 'DHH Teacher');
	$replacer($implementers, 'AI Teacher', 'Teacher of Visually Impaired');
	$replacer($implementers, 'LSSP', 'School Psychologist');
	$replacer($implementers, 'Counselor', 'School Counselor');

	$implementers = DBEscapeString(json_encode($implementers));

	Database::query("UPDATE sss_goals SET responsible_implementer = '{$implementers}' WHERE id = {$goal_id}");
}
