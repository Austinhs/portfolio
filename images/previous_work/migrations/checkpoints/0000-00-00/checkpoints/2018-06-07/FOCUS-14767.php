<?php
if(!Database::indexExists('gradebook_custom_grades','gradebook_custom_grades_ind1'))
	Database::query("create index gradebook_custom_grades_ind1 on gradebook_custom_grades (staff_id)");

if(Database::$type=='postgres' && !Database::indexExists('standards_join_courses','standards_join_courses_course_num17'))
	Database::query("create index standards_join_courses_course_num17 on standards_join_courses (substring(course_num,1,7))");