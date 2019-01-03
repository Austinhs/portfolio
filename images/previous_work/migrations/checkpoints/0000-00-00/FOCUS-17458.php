<?php

if(!Database::indexExists('custom_field_log_entries','custom_field_log_entries_source_id_numeric') && Database::$type=='postgres')
	Database::query("create index custom_field_log_entries_source_id_numeric on custom_field_log_entries (cast(source_id as numeric))");
	
if(!Database::indexExists('grade_posting_schemes','grade_posting_schemes_ind1'))
	Database::query("create index grade_posting_schemes_ind1 on grade_posting_schemes (syear,school_id)");
	
	
if(!Database::indexExists('course_teacher_history','course_teacher_history_ind1'))
	Database::query("create index course_teacher_history_ind1 on course_teacher_history (staff_id)");
if(!Database::indexExists('course_teacher_history','course_teacher_history_ind1'))
	Database::query("create index course_teacher_history_ind2 on course_teacher_history (course_period_id)");
	
