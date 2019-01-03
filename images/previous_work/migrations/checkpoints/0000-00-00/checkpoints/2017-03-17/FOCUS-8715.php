<?php
//Update Keys
Database::query("update importer_keys set required_fields = '[\"syear\",\"teacher_id\"]' where TABLE_NAME = 'course_periods';");
Database::query("update importer_keys set primary_keys = '[\"school_id\",\"course_id\",\"short_name\",\"marking_period_id\"]' where TABLE_NAME = 'course_periods'; ");
Database::query("update importer_keys set required_fields = '[]' where TABLE_NAME = 'schedule';");
Database::query("update importer_keys set required_fields = '[\"credits_for_import\",\"subject_id\",\"mp\"]' where TABLE_NAME = 'courses'; ");
Database::query("update importer_keys set required_fields = '[\"include_in_class_rank\"]' where TABLE_NAME = 'student_enrollment';");
Database::query("update importer_keys set primary_keys = '[\"student_id\",\"school_id\",\"syear\",\"start_date\"]' where TABLE_NAME = 'student_enrollment'; ") ;
Database::query("update importer_keys set required_fields = '[\"student_id\",\"administration_date\",\"test_id\",\"part_id\"]' where TABLE_NAME = 'test_history_importer_table';");
Database::query("update importer_keys set primary_keys = '[\"score_type\",\"score_value\"]' WHERE TABLE_NAME = '  test_history_importer_table';");
Database::query("update importer_templates set name = 'Attendance Calendar' where name = 'Attendance Calander';");


//Create Test History Importer Table
if (Database::tableExists('test_history_importer_table')){
	Database::query('drop table test_history_importer_table;');
}

Database::query('
	create table test_history_importer_table(
	administration_date date,
	administration_id numeric,
	custom_1 varchar(20),
	custom_2 varchar(20),
	custom_3 varchar(20),
	data_type varchar(255),
	djj_info varchar(50),
	fas_assignment_id numeric,
	gradelevel varchar(10),
	imported varchar(1),
	lep_info varchar(10),
	part_id numeric, 
	score_type numeric,
	score_value varchar(255),
	student_id numeric,
	syear numeric,     
	test_form varchar(255),
	test_id numeric,
	test_level varchar(255),
	transcript varchar(1)
	)
');

