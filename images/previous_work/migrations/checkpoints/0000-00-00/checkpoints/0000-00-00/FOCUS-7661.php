<?php

if($GLOBALS['DatabaseType']=='postgres' && !Database::indexExists('test_history_parts', 'test_history_parts_sort'))
{
	Database::query("CREATE INDEX test_history_parts_sort ON test_history_parts USING btree (sort_order, title)");
}

Database::query("delete from students_join_people where not exists (select '' from people p where p.person_id=students_join_people.person_id)");
Database::query("delete from students_join_address where not exists (select '' from address a where a.address_id=students_join_address.address_id)");
Database::query("delete from students_join_users where not exists (select '' from users u where u.staff_id=students_join_users.staff_id)");

Database::query("delete from school_period_bell_times where not exists (select '' from school_period_bell_schedules s,school_periods sp where s.id=school_period_bell_times.bell_schedule_id and sp.period_id=school_period_bell_times.period_id and sp.school_id=school_period_bell_times.school_id and s.school_id=sp.school_id)");

if(!Database::columnExists('attendance_completed','last_updated_date'))
{
	$timestamp_type = ($GLOBALS['DatabaseType']=='postgres') ? 'timestamp' : 'datetime';
	Database::query("alter table attendance_completed add last_updated_date ".$timestamp_type);
}

if(!Database::tableExists('scheduler_info'))
{
	if($GLOBALS['DatabaseType']=='postgres')
	{
		Database::query("CREATE TABLE scheduler_info
		(
		  school_id numeric NOT NULL,
		  syear numeric NOT NULL,
		  run varchar(20) NOT NULL,
		  infotype varchar(20) NOT NULL,
		  text1 varchar(255) NULL,
		  text2 varchar(255) NULL,
		  text3 varchar(255) NULL,
		  text4 varchar(255) NULL,
		  text5 varchar(255) NULL,
		  int1 integer NULL,
		  int2 integer NULL,
		  int3 integer NULL,
		  int4 integer NULL,
		  int5 integer NULL,
		  infokey varchar(255) NOT NULL
		)");

		Database::query("alter table scheduler_info add primary key (school_id, syear, run, infotype, infokey)");

		Database::query("CREATE INDEX infoindex1 ON scheduler_info (school_id, syear, run, infotype)");

		if(!Database::tableExists('scheduler_sections'))
		{
			Database::query("CREATE TABLE scheduler_sections(
			  school_id numeric NOT NULL,
			  syear numeric NOT NULL,
			  run varchar(50) NOT NULL,
			  staff_id numeric NOT NULL,
			  course_id numeric NOT NULL,
			  period_id numeric null,
			  max_seats integer NOT NULL,
			  available_seats integer null,
			  infokey varchar(50) NOT NULL,
			  rooms varchar null,
			  override_period_id numeric null,
			  mp_level character(4) null,
			  mp numeric null,
			  followup_course varchar(50) null,
			  days character(8) null,
			  short_name character(10) null,
			  does_attendance character(1) null,
			  does_grades character(1) null,
			  does_gpa character(1) null,
			  gender_restriction character(1) null,
			  grading_scale_id numeric null,
			  calendar_id numeric null,
			  course_period_id numeric null,
			  parent_id varchar(50) null,
				period_set_by varchar(200) NULL,
				sessions int NULL,
				number_of_doubles int NULL,
				periods_spanned int NULL,
				rotation_days varchar(50) NULL,
				combine_sections varchar(1000) NULL,
				end_period_id numeric(18, 0) NULL,
				meeting_days varchar(50) NULL,
				course_weight varchar(50) NULL,
				workCourseIdWeight varchar(150) NULL
			)");


			Database::query("CREATE INDEX CourseKey ON scheduler_sections (course_id, syear)");

			Database::query("CREATE INDEX RunIndex ON scheduler_sections (school_id, syear, run)");
		}

		if(!Database::tableExists('scheduler_shared_courses'))
		{
			Database::query("CREATE TABLE scheduler_shared_courses(
				school_id int NOT NULL,
				syear int NOT NULL,
				run varchar(20) NOT NULL,
				parent_course varchar(150) NOT NULL,
				shared_course varchar(150) NOT NULL)");

			Database::query("alter table scheduler_shared_courses add primary key (school_id,syear,run,parent_course,shared_course)");
		}

		if(!Database::tableExists('scheduler_course_defaults'))
		{
			Database::query("CREATE TABLE scheduler_course_defaults(
				course_id numeric(18, 0) NOT NULL,
				course_weight varchar(50) not null,
				school_id numeric(18, 0) NOT NULL,
				syear numeric(18, 0) NOT NULL,
				calendar_id numeric(18, 0) NULL,
				calendar_id_status char(1) NULL,
				days nchar(8) NULL,
				days_status char(1) NULL,
				does_attendance char(1) NULL,
				does_attendance_status char(1) NULL,
				does_grades char(1) NULL,
				does_grades_status char(1) NULL,
				does_gpa char(1) NULL,
				does_gpa_status char(1) NULL,
				grade_scale_id numeric(18, 0) NULL,
				grade_scale_id_status char(1) NULL)");

			Database::query("alter table scheduler_course_defaults add PRIMARY KEY (
				course_id,
				course_weight,
				school_id,
				syear
			)");
		}

		if(!Database::columnExists('course_periods','scheduler_link'))
			Database::query("alter table course_periods add scheduler_link varchar(30)");

		if(!Database::tableExists('scheduler_block_requests'))
		{
			Database::query("CREATE TABLE scheduler_block_requests(
			 school_id numeric NOT NULL,
			 syear numeric NOT NULL,
			 run varchar(50)  NOT NULL,
			 infokey varchar(50)  NOT NULL,
			 block varchar(50)  NOT NULL,
			 period_id numeric NOT NULL,
			 count numeric NULL
			)");

			Database::query("ALTER TABLE scheduler_block_requests add primary key
			(
			 school_id ,
			 syear,
			 run ,
			 infokey ,
			 block ,
			 period_id
			)");
		}

		if(!Database::tableExists('scheduler_block_sections'))
		{
			Database::query("CREATE TABLE scheduler_block_sections(
			 scheduler_section_parent varchar(50)  NULL,
			 school_id numeric NOT NULL,
			 syear numeric NOT NULL,
			 run varchar(50)  NOT NULL,
			 staff_id numeric NOT NULL,
			 course_id numeric NOT NULL,
			 period_id numeric NULL,
			 max_seats int NOT NULL,
			 available_seats int NULL,
			 infokey varchar(50)  NULL,
			 rooms varchar(50)  NULL,
			 override_period_id numeric NULL,
			 mp_level char(4)  NULL,
			 mp numeric NULL,
			 followup_course varchar(50) NULL,
			 days char(8)  NULL,
			 short_name char(10)  NULL,
			 does_attendance char(1)  NULL,
			 does_grades char(1)  NULL,
			 gender_restriction char(1)  NULL,
			 grading_scale_id numeric NULL,
			 calendar_id numeric NULL,
			 course_period_id numeric NULL,
			 parent_id varchar(50)  NULL,
			 does_gpa char(1)  NULL,
			 period_set_by varchar(200)  NULL
			)");

			Database::query("CREATE INDEX block_run ON scheduler_block_sections
			(
			 school_id ,
			 syear ,
			 run ,
			 course_period_id
			)");

			Database::query("CREATE INDEX infokey ON scheduler_block_sections
			(
			 infokey
			)");
		}
	}
	else
	{
		Database::query("CREATE TABLE scheduler_info
		(
		  school_id numeric NOT NULL,
		  syear numeric NOT NULL,
		  run character varying(20) NOT NULL,
		  infotype character varying(20) NOT NULL,
		  text1 character varying(50),
		  text2 character varying(50),
		  text3 character varying(50),
		  text4 character varying(50),
		  text5 character varying(50),
		  int1 integer,
		  int2 integer,
		  int3 integer,
		  int4 integer,
		  int5 integer,
		  infokey character varying(50) NOT NULL,
		  CONSTRAINT \"schedulerInfoPrimaryKey\" PRIMARY KEY (school_id, syear, run, infotype, infokey)
		)");

		Database::query("CREATE INDEX infoindex1 ON scheduler_info (school_id, syear, run, infotype)");

		Database::query("CREATE TABLE scheduler_sections
		(
		  school_id numeric NOT NULL,
		  syear numeric NOT NULL,
		  run character varying(50) NOT NULL,
		  staff_id numeric NOT NULL,
		  course_id numeric NOT NULL,
		  period_id numeric,
		  max_seats integer NOT NULL,
		  available_seats integer,
		  infokey character varying(50) NOT NULL,
		  rooms character varying,
		  override_period_id numeric,
		  mp_level character(4),
		  mp numeric,
		  followup_course numeric,
		  days character(8),
		  short_name character(10),
		  does_attendance character(1),
		  does_grades character(1),
		  does_gpa character(1),
		  gender_restriction character(1),
		  grading_scale_id numeric,
		  calendar_id numeric,
		  course_period_id numeric,
		  parent_id character varying(50),
		  CONSTRAINT \"schedulerSectionsPrimaryKey\" PRIMARY KEY (infokey)
		)");

		Database::query("CREATE INDEX CourseKey  ON scheduler_sections (course_id, syear)");

		Database::query("CREATE INDEX RunIndex ON scheduler_sections (school_id, syear, run)");
	}
}
?>
