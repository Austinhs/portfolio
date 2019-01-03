<?php
	if(!Database::tableExists('standards_grades_completed')) {
		$query = "
			CREATE TABLE standards_grades_completed (
				course_period_id NUMERIC,
				marking_period_id VARCHAR(10),
				period_id NUMERIC,
				staff_id NUMERIC NOT NULL
			)
		";

		Database::query($query);
	}
	
$some_dates_already_set_RET = Database::get("select count(*) as count from school_quarters mp where post_start_date is not null and syear>=2018");
if($some_dates_already_set_RET[0]['COUNT']==0) {
	DBQuery("update school_years mp set stand_post_start_date=post_start_date, stand_post_end_date=post_end_date
		where syear>=2018 and stand_post_start_date is null and stand_post_end_date is null 
		and post_start_date is not null and post_end_date is not null
		and exists (select '' from courses c,standards_join_courses sjc,standards s where s.id=sjc.standard_id and sjc.course_num=c.short_name and c.syear=mp.syear and c.school_id=mp.school_id)");

	DBQuery("update school_semesters mp set stand_post_start_date=post_start_date, stand_post_end_date=post_end_date
		where syear>=2018 and stand_post_start_date is null and stand_post_end_date is null 
		and post_start_date is not null and post_end_date is not null
		and exists (select '' from courses c,standards_join_courses sjc,standards s where s.id=sjc.standard_id and sjc.course_num=c.short_name and c.syear=mp.syear and c.school_id=mp.school_id)");

	DBQuery("update school_quarters mp set stand_post_start_date=post_start_date, stand_post_end_date=post_end_date
		where syear>=2018 and stand_post_start_date is null and stand_post_end_date is null 
		and post_start_date is not null and post_end_date is not null
		and exists (select '' from courses c,standards_join_courses sjc,standards s where s.id=sjc.standard_id and sjc.course_num=c.short_name and c.syear=mp.syear and c.school_id=mp.school_id)");

	DBQuery("update school_progress_periods mp set stand_post_start_date=post_start_date, stand_post_end_date=post_end_date
		where syear>=2018 and stand_post_start_date is null and stand_post_end_date is null 
		and post_start_date is not null and post_end_date is not null
		and exists (select '' from courses c,standards_join_courses sjc,standards s where s.id=sjc.standard_id and sjc.course_num=c.short_name and c.syear=mp.syear and c.school_id=mp.school_id)");
}