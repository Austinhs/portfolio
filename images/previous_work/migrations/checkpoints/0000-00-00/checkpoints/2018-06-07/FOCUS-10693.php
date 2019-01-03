<?php

if(Database::$type === 'mssql' && !Database::indexExists('users', 'users_staff_id_plus', null, 1)) {
	Database::query("CREATE NONCLUSTERED INDEX users_staff_id_plus ON users (staff_id ASC) INCLUDE (first_name, last_name, middle_name, username)");
	Database::query("CREATE NONCLUSTERED INDEX school_semesters_schid_syr_mrkperid_stdt_enddt ON school_semesters (school_id ASC, syear ASC, marking_period_id ASC, start_date ASC, end_date ASC)");
	Database::query("CREATE NONCLUSTERED INDEX school_quarters_schid_syr_mrkperid_stdt_enddt ON school_quarters (school_id ASC, syear ASC, marking_period_id ASC, start_date ASC, end_date ASC)");
	Database::query("CREATE NONCLUSTERED INDEX school_periods_att_perid_blk ON school_periods (attendance ASC, period_id ASC, block ASC) INCLUDE (title, short_name)");
	Database::query("CREATE NONCLUSTERED INDEX attendance_completed_crsperid_schdt_perid ON attendance_completed (course_period_id ASC, school_date ASC, period_id ASC)");
}
