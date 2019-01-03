<?php

if(!Database::columnExists('attendance_scheduled_hours_override', 'START_TIME')) {
	Database::createColumn('attendance_scheduled_hours_override', 'START_TIME', 'varchar', 10);
}

if(!Database::columnExists('attendance_scheduled_hours_override', 'END_TIME')) {
	Database::createColumn('attendance_scheduled_hours_override', 'END_TIME', 'varchar', 10);
}