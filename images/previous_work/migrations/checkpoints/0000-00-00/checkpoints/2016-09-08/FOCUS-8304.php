<?php

//Create Test History Importer Table 

Database::query("
	UPDATE importer_keys set post_sql = '[\"
				UPDATE student_enrollment se
				SET calendar_id = ac.calendar_id
				FROM
					attendance_calendars ac
				WHERE
					se.school_id=ac.school_id
					AND se.syear=ac.syear
					AND ac.default_calendar=''Y''\"]'
	WHERE table_name = 'student_enrollment'
");