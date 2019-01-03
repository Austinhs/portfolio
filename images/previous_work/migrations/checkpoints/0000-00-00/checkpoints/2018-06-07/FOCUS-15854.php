<?php
Migrations::depend('FOCUS-16915');

if(Database::$type=='postgres')
{
	Database::query("update course_periods set custom_18=cast(cast(custom_18 as date) as varchar(20)) where custom_18 is not null");
	Database::query("update course_periods set custom_17=cast(cast(custom_17 as date) as varchar(20)) where custom_17 is not null");
}
