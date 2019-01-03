<?php

Database::isolate(function() {

	if($GLOBALS['DatabaseType']=='postgres')
	{
		if(!Database::indexExists('fas_discussion_posts','fas_discussion_posts_pkey'))
			Database::query("alter table fas_discussion_posts add constraint fas_discussion_posts_pkey primary key (id)");
		if(!Database::indexExists('school_period_bell_times','school_period_bell_times_pkey'))
			Database::query("alter table school_period_bell_times add primary key (bell_schedule_id,period_id)");
		// for some reason this takes forever within a migration
		//$concurrently = 'concurrently';
	}
	elseif(!Database::indexExists('school_period_bell_times','school_period_bell_times_pkey'))
	{
		if(!Database::columnExists('school_period_bell_times','id'))
			Database::createColumn('school_period_bell_times','id','numeric');

		Database::query("CREATE INDEX {$concurrently} school_period_bell_times_pkey ON school_period_bell_times (id)");
		$concurrently = '';
	}

	if(!Database::indexExists('gradebook_student_numbers','gradebook_student_numbers_ind1'))
		Database::query("create index {$concurrently} gradebook_student_numbers_ind1 on gradebook_student_numbers (student_id)");

	if(!Database::indexExists('gradebook_student_numbers','gradebook_student_numbers_ind2'))
		Database::query("create index {$concurrently} gradebook_student_numbers_ind2 on gradebook_student_numbers (course_period_id)");

	if(!Database::indexExists('saved_reports','saved_reports_ind1'))
		Database::query("create index {$concurrently} saved_reports_ind1 on saved_reports (staff_id)");

	if(!Database::indexExists('saved_reports','saved_reports_ind2'))
		Database::query("create index {$concurrently} saved_reports_ind2 on saved_reports (school_id)");

	if(!Database::indexExists('assignment_alerts','assignment_alerts_ind1'))
		Database::query("create index {$concurrently} assignment_alerts_ind1 on assignment_alerts (assignment_id)");

	if(!Database::indexExists('grades_completed','grades_completed_ind2'))
		Database::query("create index {$concurrently} grades_completed_ind2 on grades_completed (course_period_id)");

	if(!Database::indexExists('grades_completed','grades_completed_ind3'))
		Database::query("create index {$concurrently} grades_completed_ind3 on grades_completed (marking_period_id)");

	if(!Database::indexExists('fas_discussion_post_alerts','fas_discussion_post_alerts_ind1'))
		Database::query("create index {$concurrently} fas_discussion_post_alerts_ind1 on fas_discussion_post_alerts (assignment_id)");

	if(!Database::indexExists('translations','translations_ind1'))
		Database::query("create index {$concurrently} translations_ind1 on translations (language_id)");

	if(!Database::indexExists('translations','translations_ind2'))
		Database::query("create index {$concurrently} translations_ind2 on translations (original)");

	if(!Database::indexExists('languages','languages_ind1'))
		Database::query("create index {$concurrently} languages_ind1 on languages (title)");

	if(!Database::indexExists('student_gpa_calculated','student_gpa_calculated_ind4'))
		Database::query("CREATE INDEX {$concurrently} student_gpa_calculated_ind4 ON student_gpa_calculated (syear,school_id)");

	if(!Database::indexExists('standards','standards_ind5'))
		Database::query("create index {$concurrently} standards_ind5 on standards (syear)");

	if(!Database::indexExists('discipline_referrals','discipline_referrals_ind8'))
		Database::query("create index {$concurrently} discipline_referrals_ind8 on discipline_referrals (merged_referral_id)");

	if(!Database::indexExists('discipline_incidents_join_referrals','discipline_incidents_join_referrals_ind1'))
		Database::query("create index {$concurrently} discipline_incidents_join_referrals_ind1 on discipline_incidents_join_referrals (incident_id)");

	if(!Database::indexExists('discipline_incidents_join_referrals','discipline_incidents_join_referrals_ind2'))
		Database::query("create index {$concurrently} discipline_incidents_join_referrals_ind2 on discipline_incidents_join_referrals (referral_id)");

	if(!Database::indexExists('discipline_referrals_field_usage','discipline_referrals_field_usage_ind1'))
		Database::query("create index {$concurrently} discipline_referrals_field_usage_ind1 on discipline_referrals_field_usage (syear,school_id)");

});