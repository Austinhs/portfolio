<?php

if (!Database::columnExists('course_periods', 'inclusion_minutes')) {
	Database::createColumn('course_periods', 'inclusion_minutes', 'int');
}

if (!Database::columnExists('course_periods', 'inclusion_rotation')) {
	Database::createColumn('course_periods', 'inclusion_rotation', 'varchar');
}

if (!Database::columnExists('schedule_inclusion_details', 'rotation_days')) {
	Database::createColumn('schedule_inclusion_details', 'rotation_days', 'varchar');
}
