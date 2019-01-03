<?php

// Depend on the 8.2.3 migration
Migrations::depend('FOCUS-7224d');

// Depend on all migrations that were added in 8.2.3
Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-6643');
Migrations::depend('FOCUS-6446');
Migrations::depend('FOCUS-6539');
Migrations::depend('FOCUS-6563');
Migrations::depend('FOCUS-6574');
Migrations::depend('FOCUS-6619');
Migrations::depend('FOCUS-6660');
Migrations::depend('FOCUS-6665');
Migrations::depend('FOCUS-6676');
Migrations::depend('FOCUS-6706');
Migrations::depend('FOCUS-6710');
Migrations::depend('FOCUS-6715');
Migrations::depend('FOCUS-6986');

echo "8.4.3 Migration";

// For general use
$text_type     = Database::$type === 'postgres' ? 'TEXT' : 'VARCHAR(MAX)';
$datetime_type = Database::$type === 'postgres' ? 'TIMESTAMP' : 'DATETIME2';
$finance       = !empty($GLOBALS['FocusFinanceConfig']['installed']);

// BEGIN FOCUS-6533
if(!Database::columnExists('users', 'password_token')) {
	Database::createColumn('users', 'password_token', 'varchar', 100);
}
// END FOCUS-6533

if($finance) {
	// Change slot_id to a json array
	if(!Database::columnExists('gl_pr_positions', 'slots')) {
		Database::query("
			ALTER TABLE gl_pr_positions ADD slots {$text_type} DEFAULT '[]'
		");

		Database::query("
			UPDATE
				gl_pr_positions
			SET
				slots = CONCAT('[', CAST(slot_id AS {$text_type}), ']')
		");
	}

	if(!Database::columnExists('gl_pr_positions', 'jobs')) {
		Database::query("
			ALTER TABLE gl_pr_positions ADD jobs {$text_type} DEFAULT '[]'
		");

		Database::query("
			UPDATE gl_pr_positions SET jobs = job
		");
	}

	if(!Database::columnExists('gl_hr_job_posting', 'job')) {
		Database::createColumn('gl_hr_job_posting', 'job', 'varchar', 50);
	}

	if(!Database::columnExists('gl_pr_positions', 'budgeted_salary')) {
		Database::query("
			ALTER TABLE gl_pr_positions ADD budgeted_salary NUMERIC DEFAULT 0
		");
	}

	if(!Database::columnExists('gl_pr_positions', 'deductions')) {
		Database::query("
			ALTER TABLE gl_pr_positions ADD deductions {$text_type} DEFAULT '[]'
		");
	}

	if(!Database::columnExists('gl_pr_staff_job_positions', 'jobs')) {
		Database::query("
			ALTER TABLE gl_pr_staff_job_positions ADD jobs {$text_type} DEFAULT '[]'
		");

		Database::query("
			UPDATE
				gl_pr_staff_job_positions
			SET
				jobs = CONCAT('[', CAST(job_id AS {$text_type}), ']')
		");

		Database::query("
			DELETE FROM
				gl_setting
			WHERE
				\"key\" = 'show_job_positions'
		");
	}
}

// Delete 'StudentFieldsView' config records for holder, log, and file fields
Database::query("
	DELETE FROM
		program_user_config
	WHERE
		program = 'StudentFieldsView' AND
		title IN (
			SELECT
				CAST(id AS VARCHAR)
			FROM
				custom_fields
			WHERE
				source_class = 'SISStudent' AND
				type IN ('holder','log','file')
		)
");

// Delete 'StudentFieldsView' config records for form fields
Database::query("
	DELETE FROM
		program_user_config
	WHERE
		program = 'StudentFieldsView' AND
		title IN (
			SELECT
				CAST(cf.id AS VARCHAR)
			FROM
				custom_fields cf,
				custom_fields_join_categories j,
				custom_field_categories c
			WHERE
				c.id = j.category_id AND
				cf.id = j.field_id AND
				cf.source_class = 'SISStudent' AND
				c.form = '1'
		)
");

// BEGIN FOCUS-6937
if(Database::$type === 'mssql') {
	if(Database::tableExists('marking_periods')) {
		Database::query("
			DROP TABLE marking_periods
		");

		$triggers = [
			'School_Semesters'        => 'School_Semesters_ID_Trig',
			'School_Years'            => 'School_years_ID_Trig',
			'School_Quarters'         => 'School_quarters_ID_Trig',
			'School_progress_periods' => 'School_progress_periods_ID_Trig',
		];

		foreach($triggers as $table => $trigger) {
			Database::query("
				DISABLE TRIGGER {$trigger} ON {$table}
			");
		}
	}
}
// END FOCUS-6937


// BEGIN FOCUS-6698
// Table + indexes for Attendance Subs
if(!Database::tableExists('attendance_subs')) {
	Database::query("
		CREATE TABLE attendance_subs (
			id BIGINT PRIMARY KEY,
			start_date DATE NOT NULL,
			end_date DATE NOT NULL,
			course_period_id BIGINT NOT NULL,
			staff_id BIGINT NOT NULL,
			created_date {$datetime_type} DEFAULT CURRENT_TIMESTAMP NOT NULL,
			created_by BIGINT NOT NULL
		);
	");
}

if(!Database::sequenceExists('attendance_subs_seq')) {
	Database::createSequence('attendance_subs_seq');
}

$existing = Database::getIndexes('attendance_subs');

$indexes = [
	'attendance_subs_id_index'               => ['id'],
	'attendance_subs_dates_index'            => ['start_date', 'end_date'],
	'attendance_subs_course_period_id_index' => ['course_period_id'],
	'attendance_subs_staff_id_index'         => ['staff_id']
];

foreach($indexes as $index => $columns) {
	if(empty($existing[$index])) {
		$columns = join(', ', $columns);

		Database::query("
			CREATE INDEX {$index} ON attendance_subs ({$columns})
		");
	}
}

if(!Database::columnExists('attendance_codes', 'sub')) {
	Database::createColumn('attendance_codes', 'sub', 'varchar', 1);
}

// --------------------------------------------------------------
// -- Convert Permissions for new Attendance/Setup.php page
// --------------------------------------------------------------

$permissions = [
	// Attendance/CalculateHours.php migration
	'Attendance/CalculateHours.php:can_view' => 'SIS:Attendance:RecalcHours',
	'Attendance/CalculateHours.php:can_edit' => 'SIS:Attendance:RecalcHours',
	'Attendance/AttendanceCodes.php:can_view' => 'SIS:Attendance:Codes:View',
	'Attendance/AttendanceCodes.php:can_edit' => 'SIS:Attendance:Codes:Edit',
	'Attendance/ScheduledHoursOverride.php:can_view' => 'SIS:Attendance:HoursOverride:View',
	'Attendance/ScheduledHoursOverride.php:can_edit' => 'SIS:Attendance:HoursOverride:Edit',

	// Attendance/FixDailyAttendance.php migration
	'Attendance/FixDailyAttendance.php:can_view' => 'SIS:Attendance:RecalcDaily',
	'Attendance/FixDailyAttendance.php:can_edit' => 'SIS:Attendance:RecalcDaily'
];

foreach($permissions as $old_key => $new_key) {
	$sql = "
		UPDATE
			permission
		SET
			\"key\" = :new_key
		WHERE
			\"key\" = :old_key AND
			NOT EXISTS (
				SELECT
					1
				FROM
					permission p
				WHERE
					p.\"key\" = :new_key AND
					p.profile_id = profile_id
			)
	";

	$params = [
		'old_key' => $old_key,
		'new_key' => $new_key
	];

	Database::query($sql, $params);
}

Database::query("
	INSERT INTO permission (
		profile_id, \"key\"
	)
	SELECT
		perm.profile_id,
		perm.\"key\"
	FROM
		(
			SELECT DISTINCT
				p1.profile_id,
				'Attendance/Setup.php:can_view' AS \"key\"
			FROM
				permission p1
			WHERE
				p1.\"key\" IN (
					'SIS:Attendance:RecalcHours',
					'SIS:Attendance:Codes:View',
					'SIS:Attendance:Codes:Edit',
					'SIS:Attendance:HoursOverride:View',
					'SIS:Attendance:HoursOverride:Edit',
					'SIS:Attendance:RecalcDaily'
				) AND
				NOT EXISTS (
					SELECT
						1
					FROM
						permission p2
					WHERE
						p2.\"key\" = 'Attendance/Setup.php:can_view' AND
						p2.profile_id = p1.profile_id
				)
		) perm
");
// END FOCUS-6698

// BEGIN FOCUS-6901
if(!Database::tableExists('district_report_runlog')) {
	Database::query("
		CREATE TABLE district_report_runlog (
			id BIGINT PRIMARY KEY,
			staff_id BIGINT NULL,
			logged_in_as BIGINT NULL,
			report_id BIGINT NULL,
			start_time {$datetime_type},
			end_time {$datetime_type},
			syear INTEGER,
			school_id INTEGER,
			query {$text_type},
			records_affected BIGINT
		);
	");
}
// END FOCUS-6901

// BEGIN FOCUS-7000/FOCUS-4600
if(!Database::columnExists('school_choice_programs', 'seats_pk')) {
	Database::createColumn('school_choice_programs', 'seats_pk', 'numeric');
}

Database::changeColumnType('school_choice_application_status', 'priority', 'numeric');

if(!Database::columnExists('school_choice_application_status', 'student_id')) {
	Database::createColumn('school_choice_application_status', 'student_id', 'varchar', 32);
}

if(!Database::columnExists('school_choice_application_status', 'applying_program_id')) {
	Database::createColumn('school_choice_application_status', 'applying_program_id', 'numeric');
}

if(!Database::columnExists('school_choice_application_status', 'syear')) {
	Database::createColumn('school_choice_application_status', 'syear', 'numeric');
}

if(!Database::columnExists('school_choice_application_status', 'projected_grade')) {
	Database::createColumn('school_choice_application_status', 'projected_grade', 'varchar', 16);
}

if(Database::columnExists('school_choice_application_status', 'verify')) {
	Database::query("
		ALTER TABLE school_choice_application_status DROP COLUMN verify
	");
}

if(!Database::columnExists('school_choice_applications', 'verify')) {
	Database::createColumn('school_choice_applications', 'verify', 'varchar', 1000);
}

if(!Database::columnExists('school_choice_programs', 'syear')) {
	Database::createColumn('school_choice_programs', 'syear', 'numeric');
}

if(!Database::columnExists('school_choice_programs', 'gifted')) {
	Database::createColumn('school_choice_programs', 'gifted', 'varchar', 1);
}

if(!Database::columnExists('school_choice_programs', 'exclude_gifted')) {
	Database::createColumn('school_choice_programs', 'exclude_gifted', 'varchar', 1);
}

if(!Database::columnExists('school_choice_priority_charts', 'syear')) {
	Database::createColumn('school_choice_priority_charts', 'syear', 'numeric');
}

if(!Database::columnExists('school_choice_priority_charts', 'syear')) {
	Database::createColumn('school_choice_priority_charts', 'syear', 'numeric');
}

Database::changeColumnType('school_choice_priority_charts', 'programs', 'text');

if(!Database::columnExists('school_choice_programs', 'zones')) {
	Database::createColumn('school_choice_programs', 'zones', 'varchar');
}

if(!Database::tableExists('school_choice_zones')) {
	Database::query("
		CREATE TABLE school_choice_zones (
			id BIGINT PRIMARY KEY,
			zone NUMERIC NULL,
			title VARCHAR(255) NULL
		)
	");
}

if(!Database::columnExists('school_choice_program_continuities', 'gifted')) {
	Database::createColumn('school_choice_program_continuities', 'gifted', 'varchar', 1);
}

Database::changeColumnType('school_choice_application_status', 'student_id', 'numeric', '(10, 0)');

if(Database::columnExists('school_choice_tours_auditions', 'program')) {
	Database::query("
		ALTER TABLE school_choice_tours_auditions DROP COLUMN program
	");
}

if(!Database::columnExists('school_choice_tours_auditions', 'school')) {
	Database::createColumn('school_choice_tours_auditions', 'school', 'numeric');
}

if(!Database::columnExists('school_choice_program_continuities', 'grade_levels')) {
	Database::createColumn('school_choice_program_continuities', 'grade_levels', 'varchar');
}

if(!Database::columnExists('school_choice_zones', 'schools')) {
	Database::createColumn('school_choice_zones', 'schools', 'varchar', 1024);
}

if(!Database::columnExists('school_choice_application_status', 'choice')) {
	Database::createColumn('school_choice_application_status', 'choice', 'numeric');
}

if(!Database::columnExists('school_choice_programs', 'fcat_requirement')) {
	Database::createColumn('school_choice_programs', 'fcat_requirement', 'varchar', 1);
}

if(!Database::columnExists('school_choice_application_status', 'current_school')) {
	Database::createColumn('school_choice_application_status', 'current_school', 'numeric');
}

if(!Database::columnExists('school_choice_application_status', 'current_program')) {
	Database::createColumn('school_choice_application_status', 'current_program', 'numeric');
}

if(!Database::columnExists('school_choice_application_status', 'current_grade')) {
	Database::createColumn('school_choice_application_status', 'current_grade', 'varchar', 16);
}

if(!Database::columnExists('school_choice_application_status', 'siblings_verified')) {
	Database::createColumn('school_choice_application_status', 'siblings_verified', 'varchar', 1);
}

if(!Database::columnExists('school_choice_application_status', 'submitted_by')) {
	Database::createColumn('school_choice_application_status', 'submitted_by', 'numeric');
}

if(!Database::columnExists('school_choice_application_status', 'priority_overwrite')) {
	Database::createColumn('school_choice_application_status', 'priority_overwrite', 'numeric');
}

if(!Database::sequenceExists('school_choice_application_status_seq')) {
	Database::createSequence('school_choice_application_status_seq');
}

if(!Database::columnExists('school_choice_application_status', 'id')) {
	// Add an ID column
	Database::createColumn('school_choice_application_status', 'id', 'bigint');

	// Update to the next value of the sequence
	$sql = Database::preprocess("
		UPDATE
			school_choice_application_status
		SET
			id = {{next:school_choice_application_status_seq}}
	");

	Database::query($sql);

	// Add a primary key constraint
	Database::query("
		ALTER TABLE
			school_choice_application_status
		ADD PRIMARY KEY (id)
	");
}

if(!Database::tableExists('school_choice_application_notes')) {
	Database::query("
		CREATE TABLE school_choice_application_notes (
			id BIGINT PRIMARY KEY,
			display_text {$text_type} NULL,
			programs VARCHAR(1000) NULL,
			grades VARCHAR(1000) NULL
		)
	");
}

if(!Database::columnExists('school_choice_application_fields', 'note')) {
	Database::createColumn('school_choice_application_fields', 'note', 'text');
}

$existing = Database::get("
	SELECT
		1
	FROM
		school_choice_priorities
	WHERE
		abbr = 'GPA' AND
		title = 'Top GPA'
");

if(empty($existing)) {
	Database::query("
		INSERT INTO school_choice_priorities (
			abbr,
			title
		)
		VALUES (
			'GPA',
			'Top GPA'
		)
	");
}

if(!Database::columnExists('school_choice_application_status', 'application_id')) {
	Database::createColumn('school_choice_application_status', 'application_id', 'numeric');
}

if(Database::columnExists('school_choice_applications', 'time_submitted')) {
	Database::query("
		ALTER TABLE school_choice_applications DROP COLUMN time_submitted
	");
}

if(!Database::columnExists('school_choice_applications', 'date_submitted')) {
	Database::createColumn('school_choice_applications', 'date_submitted', 'date');
}

if(Database::columnExists('school_choice_application_status', 'siblings_verified')) {
	Database::query("
		ALTER TABLE school_choice_application_status DROP COLUMN siblings_verified
	");
}

if(!Database::columnExists('school_choice_application_status', 'siblings_verified')) {
	Database::createColumn('school_choice_application_status', 'siblings_verified', 'numeric');
}

if(!Database::sequenceExists('school_choice_program_seats_id')) {
	Database::createSequence('school_choice_program_seats_id');
}

if(!Database::tableExists('school_choice_program_seats')) {
	Database::query("
		CREATE TABLE school_choice_program_seats (
			id BIGINT PRIMARY KEY,
			program_id BIGINT NULL,
			syear INTEGER NULL,
			seats_pk NUMERIC NULL,
			seats_kg NUMERIC NULL,
			seats_01 NUMERIC NULL,
			seats_02 NUMERIC NULL,
			seats_03 NUMERIC NULL,
			seats_04 NUMERIC NULL,
			seats_05 NUMERIC NULL,
			seats_06 NUMERIC NULL,
			seats_07 NUMERIC NULL,
			seats_08 NUMERIC NULL,
			seats_09 NUMERIC NULL,
			seats_10 NUMERIC NULL,
			seats_11 NUMERIC NULL,
			seats_12 NUMERIC NULL
		);
	");

	$sql = Database::preprocess("
		INSERT INTO school_choice_program_seats (
			id,
			program_id,
			syear,
			seats_pk,
			seats_kg,
			seats_01,
			seats_02,
			seats_03,
			seats_04,
			seats_05,
			seats_06,
			seats_07,
			seats_08,
			seats_09,
			seats_10,
			seats_11,
			seats_12
		)
		SELECT
			{{next:school_choice_program_seats_id}},
			scp.id,
			scp.syear,
			scp.seats_pk,
			scp.seats_kg,
			scp.seats_01,
			scp.seats_02,
			scp.seats_03,
			scp.seats_04,
			scp.seats_05,
			scp.seats_06,
			scp.seats_07,
			scp.seats_08,
			scp.seats_09,
			scp.seats_10,
			scp.seats_11,
			scp.seats_12
		FROM
			school_choice_programs scp;
	");

	Database::query($sql);
}

$drop_columns = [
	'seats_pk',
	'seats_kg',
	'seats_01',
	'seats_02',
	'seats_03',
	'seats_04',
	'seats_05',
	'seats_06',
	'seats_07',
	'seats_08',
	'seats_09',
	'seats_10',
	'seats_11',
	'seats_12',
	'syear'
];

foreach($drop_columns as $column) {
	if(Database::columnExists('school_choice_programs', $column)) {
		Database::query("
			ALTER TABLE school_choice_programs DROP COLUMN {$column}
		");
	}
}

if(!Database::columnExists('school_choice_program_categories', 'spa')) {
	Database::createColumn('school_choice_program_categories', 'spa', 'varchar', 1);
}
// END FOCUS-7000/FOCUS-4600

// Add some columns to master_courses
if(!Database::columnExists('master_courses', 'does_grades')) {
	Database::createColumn('master_courses', 'does_grades', 'varchar', 1);
}

if(!Database::columnExists('master_courses', 'default_max_seats')) {
	Database::createColumn('master_courses', 'default_max_seats', 'numeric');
}

// Add a discipline referral field
$existing = Database::get("
	SELECT
		1
	FROM
		discipline_referrals_fields
	WHERE
		id = '2000002'
");

if(empty($existing)) {
	Database::query("
		INSERT INTO DISCIPLINE_REFERRALS_FIELDS (ID,TYPE,SEARCH,SELECT_OPTIONS,CATEGORY_ID,SYSTEM_FIELD,DEFAULT_SELECTION,SORT_ORDER,REQUIRED,SELECT_OPTION_CODES,SELECT_OPTION_DEFAULT_CODE,TITLE,ALLOW_MOD,PS_PORT_VIS,APPLICATION,FIELD_EDITS,LOG_FIELD1,LOG_FIELD1_SORT_ORDER,LOG_FIELD1_TITLE,LOG_FIELD2,LOG_FIELD2_SORT_ORDER,LOG_FIELD2_TITLE,LOG_FIELD3,LOG_FIELD3_SORT_ORDER,LOG_FIELD3_TITLE,LOG_FIELD4,LOG_FIELD4_SORT_ORDER,LOG_FIELD4_TITLE,LOG_FIELD5,LOG_FIELD5_SORT_ORDER,LOG_FIELD5_TITLE,LOG_FIELD6,LOG_FIELD6_SORT_ORDER,LOG_FIELD6_TITLE,LOG_FIELD7,LOG_FIELD7_SORT_ORDER,LOG_FIELD7_TITLE,LOG_FIELD8,LOG_FIELD8_SORT_ORDER,LOG_FIELD8_TITLE,LOG_FIELD9,LOG_FIELD9_SORT_ORDER,LOG_FIELD9_TITLE,LOG_FIELD10,LOG_FIELD10_SORT_ORDER,LOG_FIELD10_TITLE,LOG_FIELD11,LOG_FIELD11_SORT_ORDER,LOG_FIELD11_TITLE,LOG_FIELD12,LOG_FIELD12_SORT_ORDER,LOG_FIELD12_TITLE,LOG_FIELD13,LOG_FIELD13_SORT_ORDER,LOG_FIELD13_TITLE,LOG_FIELD14,LOG_FIELD14_SORT_ORDER,LOG_FIELD14_TITLE,LOG_FIELD15,LOG_FIELD15_SORT_ORDER,LOG_FIELD15_TITLE,LOG_FIELD16,LOG_FIELD16_SORT_ORDER,LOG_FIELD16_TITLE,LOG_FIELD17,LOG_FIELD17_SORT_ORDER,LOG_FIELD17_TITLE,LOG_FIELD18,LOG_FIELD18_SORT_ORDER,LOG_FIELD18_TITLE,LOG_FIELD19,LOG_FIELD19_SORT_ORDER,LOG_FIELD19_TITLE,LOG_FIELD20,LOG_FIELD20_SORT_ORDER,LOG_FIELD20_TITLE,LOG_FIELD21,LOG_FIELD21_SORT_ORDER,LOG_FIELD21_TITLE,LOG_FIELD22,LOG_FIELD22_SORT_ORDER,LOG_FIELD22_TITLE,LOG_FIELD23,LOG_FIELD23_SORT_ORDER,LOG_FIELD23_TITLE,LOG_FIELD24,LOG_FIELD24_SORT_ORDER,LOG_FIELD24_TITLE,LOG_FIELD25,LOG_FIELD25_SORT_ORDER,LOG_FIELD25_TITLE,LOG_FIELD26,LOG_FIELD26_SORT_ORDER,LOG_FIELD26_TITLE,LOG_FIELD27,LOG_FIELD27_SORT_ORDER,LOG_FIELD27_TITLE,LOG_FIELD28,LOG_FIELD28_SORT_ORDER,LOG_FIELD28_TITLE,LOG_FIELD29,LOG_FIELD29_SORT_ORDER,LOG_FIELD29_TITLE,LOG_FIELD30,LOG_FIELD30_SORT_ORDER,LOG_FIELD30_TITLE,PARENT_FIELD_ID,LOG_TOP_HEADER,COMMENT) values('2000002','log',null,null,'1',null,null,'-1',null,null,null,'Victims',null,null,'Y',null,'numeric','1','Student ID','date','2','Start Date','date','3',null,'textarea','4','Comments','radio','5','Prevent Co-Enrollment',null,'6',null,null,'7',null,null,'8',null,null,'9',null,null,'10',null,null,'11',null,null,'12',null,null,'13',null,null,'14',null,null,'15',null,null,'16',null,null,'17',null,null,'18',null,null,'19',null,null,'20',null,null,'21',null,null,'22',null,null,'23',null,null,'24',null,null,'25',null,null,'26',null,null,'27',null,null,'28',null,null,'29',null,null,'30',null,null,'1',null)
	");
}

// Add some options for the new field
$existing = Database::get("
	SELECT
		1
	FROM
		logging_fields_select_options
	WHERE
		id = '2000002' AND
		html_field_title = 'referral'
");

if(empty($existing)) {
	Database::query("
		INSERT INTO LOGGING_FIELDS_SELECT_OPTIONS (ID, HTML_FIELD_TITLE, FIELD_NAME, SELECT_OPTIONS, SELECT_CODES) values
		                                ('2000002','referral','LOG_FIELD2','today',      '')
	");

	Database::query("
		INSERT INTO LOGGING_FIELDS_SELECT_OPTIONS (ID, HTML_FIELD_TITLE, FIELD_NAME, SELECT_OPTIONS, SELECT_CODES) values
		                                ('2000002','referral','LOG_FIELD3','blank',      '');
	");
}

// Is this safe to run multiple times? I don't know. - Bob M
Database::query("
	UPDATE
		course_periods
	SET
		double_blocked = NULL
	WHERE
		double_blocked IS NOT NULL
");

// BEGIN FOCUS-6442
if(!Database::columnExists('resources', 'room_description')) {
	Database::createColumn('resources', 'room_description', 'varchar', 140);
}

if(!Database::columnExists('resources', 'square_footage')) {
	Database::createColumn('resources', 'square_footage', 'integer');
}

if(!Database::columnExists('resources', 'max_syear')) {
	Database::createColumn('resources', 'max_syear', 'integer');
}

if(!Database::columnExists('resources', 'min_syear')) {
	Database::createColumn('resources', 'min_syear', 'integer');
}
// END FOCUS-6442

$existing = Database::getIndexes('attendance_calendar');

if(empty($existing['attendance_calendar_school_id_ind'])) {
	Database::query("
		CREATE INDEX attendance_calendar_school_id_ind ON attendance_calendar (school_id);
	");
}

$existing = Database::getIndexes('attendance_completed');

if(empty($existing['attendance_completed_ind1'])) {
	Database::query("
		CREATE INDEX attendance_completed_ind1 ON attendance_completed (course_period_id)
	");
}

if(empty($existing['attendance_completed_ind2'])) {
	Database::query("
		CREATE INDEX attendance_completed_ind2 ON attendance_completed (school_date)
	");
}

if(empty($existing['attendance_completed_ind3'])) {
	Database::query("
		CREATE INDEX attendance_completed_ind3 ON attendance_completed (period_id)
	");
}

if(!Database::columnExists('gradebook_grades', 'comment_codes')) {
	Database::createColumn('gradebook_grades', 'comment_codes', 'varchar');
}

if(!Database::sequenceExists('gradebook_comment_codes_seq')) {
	Database::createSequence('gradebook_comment_codes_seq');
}

if(!Database::tableExists('gradebook_comment_codes')) {
	Database::query("
		CREATE TABLE gradebook_comment_codes (
			id BIGINT PRIMARY KEY,
			code VARCHAR(20) NULL,
			schools {$text_type} NULL,
			syear NUMERIC(4) NULL,
			title VARCHAR(255) NULL
		)
	");
}

$existing = Database::get("
	SELECT
		1
	FROM
		program_config
	WHERE
		syear = 2015 AND
		program = 'system' AND
		title = 'enable_comment_codes'
");

if(empty($existing)) {
	Database::query("
		INSERT INTO program_config (
			syear,
			program,
			title,
			value
		)
		VALUES (
			2015,
			'system',
			'enable_comment_codes',
			'N'
		)
	");
}

if(!Database::columnExists('schedule_inclusion_details', 'rotation_days')) {
	Database::createColumn('schedule_inclusion_details', 'rotation_days', 'varchar', 20);
}

$existing = Database::get("
	SELECT
		1
	FROM
		focus_files_expiration
	WHERE
		source = 'Signatures'
");

if(empty($existing)) {
	Database::query("
		INSERT INTO focus_files_expiration (
			source,
			expiration
		)
		VALUES (
			'Signatures',
			0
		)
	");
}

// No reference for this query :(
Database::query("
	UPDATE
		course_periods
	SET
		mp = 'FY'
	WHERE
		mp != 'FY' AND
		mp LIKE '%FY%'
");
