<?php

if(Database::$type === 'mssql'){
	if(Database::columnExists('attendance_scheduled_hours_override', 'override_hours')) {
	    Database::changeColumnType('attendance_scheduled_hours_override', 'override_hours', 'numeric', '(16,2)');
	}
}