<?php
Database::query("delete from attendance_calendar where not exists (select '' from attendance_calendars ac where ac.school_id=attendance_calendar.school_id and ac.calendar_id=attendance_calendar.calendar_id and ac.syear=attendance_calendar.syear)");

if(Database::$type=='postgres')
{
	if(Database::indexExists('attendance_calendar','attendance_calendar_pkey'))
		Database::query("drop index attendance_calendar_pkey");

	if(!Database::getPrimaryKey('attendance_calendar'))
		Database::query("alter table attendance_calendar add primary key (calendar_id,school_date)");
}
