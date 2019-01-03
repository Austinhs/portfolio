<?php

if($GLOBALS['DatabaseType']=='postgres')
{
	Database::query("drop index if exists attendance_scheduled_hours_override_id_index");
	Database::query("delete from attendance_scheduled_hours_override where exists (select '' from attendance_scheduled_hours_override o2 where o2.course_period_id=attendance_scheduled_hours_override.course_period_id and o2.override_date=attendance_scheduled_hours_override.override_date and o2.id<attendance_scheduled_hours_override.id)");
	Database::query("create unique index attendance_scheduled_hours_override_unq_ind on attendance_scheduled_hours_override (course_period_id,override_date)");
}
else
{
	$exists = Database::get("SELECT * FROM sys.indexes WHERE name='attendance_scheduled_hours_override_id_index'");

	if(!empty($exists)) {
		Database::query("drop index attendance_scheduled_hours_override_id_index on attendance_scheduled_hours_override");
	}

	Database::query("delete from attendance_scheduled_hours_override where exists (select '' from attendance_scheduled_hours_override o2 where o2.course_period_id=attendance_scheduled_hours_override.course_period_id and o2.override_date=attendance_scheduled_hours_override.override_date and o2.id<attendance_scheduled_hours_override.id)");
	Database::query("create unique index attendance_scheduled_hours_override_unq_ind on attendance_scheduled_hours_override (course_period_id,override_date)");
}
