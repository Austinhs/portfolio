<?php

global $DatabaseType;

if($DatabaseType=='mssql')
	$text_type = 'varchar(max)';
else
	$text_type = 'text';

if(!Database::columnExists('ATTENDANCE_PERIOD', 'BREAK_TIMES'))
{
	if(Database::columnExists('ATTENDANCE_PERIOD', 'BREAK_OUT_TIME'))
	{
		Database::query("alter table ATTENDANCE_PERIOD drop column BREAK_OUT_TIME");
		Database::query("alter table ATTENDANCE_PERIOD drop column TEMP_HOURS");
	}
	Database::query("alter table ATTENDANCE_PERIOD add BREAK_TIMES ".$text_type);
}