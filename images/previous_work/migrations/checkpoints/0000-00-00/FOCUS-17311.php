<?php

if(!Database::tableExists('master_schedule_snapshots')) {
	$timestamp_type = $GLOBALS['DatabaseType'] == 'mssql' ? 'DATETIME' : 'TIMESTAMP';
	$query = "
		CREATE TABLE master_schedule_snapshots (
			id BIGINT PRIMARY KEY,
			SYEAR NUMERIC,
			SCHOOL_ID NUMERIC,
			CREATED_DATE {$timestamp_type},
			CREATED_BY BIGINT,
			DESCRIPTION VARCHAR(255),
			PERCENT_SCHEDULED NUMERIC,
			COUNT_SECTIONS INT
		)
	";
	Database::query($query);
	Database::createSequence('master_schedule_snapshots_seq');
	Database::query('create index master_schedule_snapshots_school_id_ind on master_schedule_snapshots (school_id)');
	Database::query('create index master_schedule_snapshots_created_by_ind on master_schedule_snapshots (created_by)');
}

if(!Database::tableExists('master_schedule_snapshot_course_subjects')) {
	// MSS Subjects
	if($GLOBALS['DatabaseType'] == 'mssql')
		$query = "select top 1 1 as snapshot_id,* into master_schedule_snapshot_course_subjects from course_subjects";
	else
		$query = "create table master_schedule_snapshot_course_subjects  as ".db_limit("select 1 as snapshot_id,* from course_subjects",1);
	Database::query($query);
	Database::query("delete from master_schedule_snapshot_course_subjects");
	Database::query("alter table master_schedule_snapshot_course_subjects add primary key (snapshot_id,subject_id)");
	Database::query('create index master_schedule_snapshot_course_subjects_school_id_ind on master_schedule_snapshot_course_subjects (school_id)');


	// MSS Courses
	if($GLOBALS['DatabaseType'] == 'mssql')
		$query = "select top 1 1 as snapshot_id,* into master_schedule_snapshot_courses from courses";
	else
		$query = "create table master_schedule_snapshot_courses as ".db_limit("select 1 as snapshot_id,* from courses",1);
	Database::query($query);
	Database::query("delete from master_schedule_snapshot_courses");
	Database::query("alter table master_schedule_snapshot_courses add primary key (snapshot_id,course_id)");
	Database::query('create index master_schedule_snapshot_courses_school_id_ind on master_schedule_snapshot_courses (school_id)');


	// MSS Course Weights
	if($GLOBALS['DatabaseType'] == 'mssql')
		$query = "select top 1 1 as snapshot_id,* into master_schedule_snapshot_course_weights from course_weights";
	else
		$query = "create table master_schedule_snapshot_course_weights as ".db_limit("select 1 as snapshot_id,* from course_weights",1);
	Database::query($query);
	Database::query("delete from master_schedule_snapshot_course_weights");
	Database::query("alter table master_schedule_snapshot_course_weights add primary key (snapshot_id,course_id,course_weight)");
	Database::query('create index master_schedule_snapshot_course_weights_school_id_ind on master_schedule_snapshot_course_weights (school_id)');


	// MSS CP
	if($GLOBALS['DatabaseType'] == 'mssql')
		$query = "select top 1 1 as snapshot_id,* into master_schedule_snapshot_course_periods from course_periods";
	else
		$query = "create table master_schedule_snapshot_course_periods as ".db_limit("select 1 as snapshot_id,* from course_periods",1);
	Database::query($query);
	Database::query("delete from master_schedule_snapshot_course_periods");
	Database::query("alter table master_schedule_snapshot_course_periods add primary key (snapshot_id,course_period_id)");
	Database::query('create index master_schedule_snapshot_course_periods_school_id_ind on master_schedule_snapshot_course_periods (school_id)');


	// MSS SCHEDULE
	if($GLOBALS['DatabaseType'] == 'mssql')
		$query = "select top 1 1 as snapshot_id,* into master_schedule_snapshot_schedule from schedule";
	else
		$query = "create table master_schedule_snapshot_schedule as ".db_limit("select 1 as snapshot_id,* from schedule",1);
	Database::query($query);
	Database::query("delete from master_schedule_snapshot_schedule");
	Database::query("alter table master_schedule_snapshot_schedule add primary key (snapshot_id,id)");
	Database::query('create index master_schedule_snapshot_schedule_school_id_ind on master_schedule_snapshot_schedule (school_id)');

	// Create a cron entry for this job
	$sql = Database::preprocess("
		INSERT INTO cron_jobs(
			{{postgres:id,}}
			hour,
			minute,
			sunday,
			monday,
			tuesday,
			wednesday,
			thursday,
			friday,
			saturday,
			class,
			title,
			priority
		)
		VALUES (
			{{postgres:{{next:cron_jobs_seq}},}}
			null,
			0,
			'Y',
			'Y',
			'Y',
			'Y',
			'Y',
			'Y',
			'Y',
			'MasterScheduleSnapshotsCron',
			'Master Schedule Snapshots',
			0
		)
	");

	Database::query($sql);

}

