<?php

// Tags: SSS

if(!Database::tableExists('sss_goals'))
	return false;

if (!Database::tableExists('sss_goals')) {
	return false;
}

$addColumn = function($table, $column, $type, $length) {

	if (Database::columnExists($table, $column)) {
		return true;
	}

	Database::createColumn($table, $column, $type, $length);
};

$addColumn('sss_goals', 'measurement_method', 'varchar', 255);
$addColumn('sss_goals', 'mastery_criteria', 'varchar', 255);
$addColumn('sss_goals', 'percent_accuracy', 'varchar', 255);
$addColumn('sss_goals', 'total_minutes', 'varchar', 255);
$addColumn('sss_goals', 'numerator_oppurtunities', 'varchar', 255);
$addColumn('sss_goals', 'denominator_oppurtunities', 'varchar', 255);
