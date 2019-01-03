<?php

//Database::isolate(function() {

//	if(Database::$type=='postgres')
//		$concurrently = 'concurrently';
//	else
		$concurrently = '';
	
	if(!Database::indexExists('students','students_custom_200000012_ind') && Database::columnExists('students','custom_200000012'))
		Database::query("create index $concurrently students_custom_200000012_ind on students (custom_200000012)");

	if(!Database::indexExists('discipline_referrals','discipline_referrals_staff_id_ind'))
		Database::query("create index $concurrently discipline_referrals_staff_id_ind on discipline_referrals (staff_id)");

	if(Database::indexExists('attendance_period','ap_school_date_2017_multi'))
		Database::query("Drop index $concurrently ap_school_date_2017_multi");

	if(!Database::indexExists('attendance_period','ap_school_date_2018_multi'))
		Database::query("create index $concurrently ap_school_date_2018_multi on attendance_period (school_date, student_id, attendance_code) WHERE school_date >= '2018-08-01'");

	if(Database::indexExists('attendance_day','ad_school_date_2018_multi'))
		Database::query("Drop index $concurrently ad_school_date_2018_multi");

	if(Database::$type=='postgres')
	{
		if(!Database::indexExists('attendance_day','ad_school_date_2018_student_id_ind'))
			Database::query("create index ad_school_date_2018_student_id_ind on attendance_day (student_id) where school_date>='2018-07-01'");

		if(!Database::indexExists('attendance_day','ad_school_date_2018_school_date_ind'))
			Database::query("create index ad_school_date_2018_school_date_ind on attendance_day (school_date) where school_date>='2018-07-01'");

		$RET = Database::get("select * from pg_indexes where tablename='students' and indexdef ilike '%custom_53%'");
		if(!count($RET) && Database::columnExists('students','custom_53'))
			Database::query("create index $concurrently students_custom_53_ind on students (custom_53)");

		$RET = Database::get("select * from pg_indexes where tablename='students' and indexdef ilike '%custom_159%'");
		if(!count($RET) && Database::columnExists('students','custom_159'))
			Database::query("create index $concurrently students_custom_159_ind on students (custom_159)");

		$RET = Database::get("select * from pg_indexes where tablename='students' and indexdef ilike '%custom_861%'");
		if(!count($RET) && Database::columnExists('students','custom_861'))
			Database::query("create index $concurrently students_custom_861_ind on students (custom_861)");

		$RET = Database::get("select * from pg_indexes where tablename='students' and indexdef ilike '%custom_200000003%'");
		if(!count($RET) && Database::columnExists('students','custom_200000003'))
			Database::query("create index $concurrently students_custom_200000003_ind on students  (custom_200000003)");

		$RET = Database::get("select * from pg_indexes where tablename='students' and indexdef ilike '%lower(custom_200000012%'");
		if(!count($RET) && Database::columnExists('students','custom_200000012'))
		{
			Database::query("create index $concurrently students_lower_custom_200000012_ind on students (lower(custom_200000012))");
		}
	}
//});
?>