<?php

// Depend on the 8.2.2 migration
Migrations::depend('FOCUS-7224c');

// Depend on all migrations that were added in 8.2.2
Migrations::depend('FOCUS-6327');
Migrations::depend('FOCUS-6345');
Migrations::depend('FOCUS-6410');
Migrations::depend('FOCUS-6469');
Migrations::depend('FOCUS-6602');

echo "8.2.3 Migration";

// For general use
$text_type     = Database::$type === 'postgres' ? 'TEXT' : 'VARCHAR(MAX)';
$datetime_type = Database::$type === 'postgres' ? 'TIMESTAMP' : 'DATETIME2';

Database::query("
	UPDATE
		courses
	SET
		grade_level = NULL
	WHERE
		grade_level = SUBSTRING(' ', 0, 0)
");

Database::changeColumnType('gradebook_assignment_tests', 'login_pin', 'varchar', 15, true);

// Begin FOCUS-6131
if(!Database::columnExists('ps_fa_worksheets', 'notes')) {
	Database::createColumn('ps_fa_worksheets', 'notes', 'text');
}

if(Database::columnExists('ps_fa_worksheets', 'student_carryover_hours')) {
	Database::query("
		ALTER TABLE ps_fa_worksheets DROP COLUMN student_carryover_hours
	");
}
// End FOCUS-6131

// BEGIN FOCUS-6234
if(!Database::columnExists('application', 'completed_at')) {
	Database::createColumn('application', 'completed_at', 'varchar');
}
// END FOCUS-6234

// BEGIN FOCUS-6396
if(!Database::tableExists('report_card_translations')) {
	Database::query("
		CREATE TABLE report_card_translations (
			string {$text_type},
			syear SMALLINT
		)
	");
}
// END FOCUS-6396

// Not including this: these columns were added in FOCUS-7224c (8.2.2) - Bob M
// --BEGIN FOCUS-6188--
// alter table student_gpa_calculated add custom_2_gpa numeric;
// alter table student_gpa_calculated add custom_3_gpa numeric;
// alter table student_gpa_calculated add custom_4_gpa numeric;
// alter table student_gpa_calculated add custom_5_gpa numeric;

// alter table student_gpa_calculated add custom_2_rank numeric;
// alter table student_gpa_calculated add custom_3_rank numeric;
// alter table student_gpa_calculated add custom_4_rank numeric;
// alter table student_gpa_calculated add custom_5_rank numeric;

// --END FOCUS-6188--

// BEGIN FOCUS-6012
foreach(['start_date', 'end_date'] as $column) {
	if(!Database::columnExists('student_report_card_grades', $column)) {
		Database::createColumn('student_report_card_grades', $column, 'date');
	}
}
// END FOCUS-6012


// Missing sql for some mssql sites
if(!Database::columnExists('portal_pages', 'main')) {
	Database::createColumn('portal_pages', 'main', 'numeric');
}
// End fix

$existing = Database::getIndexes('course_periods');

if(empty($existing['course_periods_ind11'])) {
	Database::query("
		CREATE INDEX
			course_periods_ind11
		ON
			course_periods (rollover_id);
	");
}

// Delete duplicate rows in program_config
$id  = uniqid();
$col = "id_{$id}";
$seq = "seq_{$id}";

// Create a fake ID column and sequence
Database::createColumn('program_config', $col, 'bigint');
Database::createSequence($seq);

// Set the ID column to the row number
$sql = Database::preprocess("
	UPDATE
		program_config
	SET
		{$col} = {{next:{$seq}}}
");

Database::query($sql);

// Delete duplicates
Database::query("
	DELETE FROM
		program_config
	WHERE
		{$col} NOT IN (
			SELECT
				MIN({$col})
			FROM
				program_config
			GROUP BY
				syear, school_id, program, title
		)
");

// Drop the old index
$existing = Database::getIndexes('program_config');

if(isset($existing['program_config_uniq1'])) {
	$on = Database::$type === 'mssql' ? " ON program_config" : '';

	Database::query("
		DROP INDEX program_config_uniq1 {$on}
	");
}

// Re-create the index
Database::query("
	CREATE UNIQUE INDEX
		program_config_uniq1
	ON program_config(syear, school_id, program, title)
");

// Drop the fake ID column and sequence
Database::query("
	ALTER TABLE
		program_config
	DROP COLUMN
		{$col}
");

Database::query("
	DROP SEQUENCE {$seq}
");

// Delete duplicate rows in school_fields
$id  = uniqid();
$col = "id_{$id}";
$seq = "seq_{$id}";

// Create a fake ID column and sequence
Database::createColumn('school_fields', $col, 'bigint');
Database::createSequence($seq);

// Set the ID column to the row number
$sql = Database::preprocess("
	UPDATE
		school_fields
	SET
		{$col} = {{next:{$seq}}}
");

Database::query($sql);

// Delete duplicates
Database::query("
	DELETE FROM
		school_fields
	WHERE
		EXISTS(
			SELECT
				1
			FROM
				school_fields cf2
			WHERE
				school_fields.id = cf2.id AND
				school_fields.{$col} < cf2.{$col}
		)
");

// Add a primary key on the "id" column
$existing = Database::getPrimaryKey('school_fields');

if(empty($existing)) {
	Database::query("
		ALTER TABLE
			school_fields
		ADD PRIMARY KEY (id)
	");
}

// Drop the fake ID column and sequence
Database::query("
	ALTER TABLE
		school_fields
	DROP COLUMN
		{$col}
");

Database::query("
	DROP SEQUENCE {$seq}
");

// No reference for this SQL :(
$sql = [
	"
		UPDATE
			schedule
		SET
			bill_by = mp.marking_period_id
		FROM
			school_semesters mp
		WHERE
			schedule.start_date BETWEEN mp.start_date AND mp.end_date
			AND schedule.school_id = mp.school_id
			AND schedule.syear = mp.syear
			AND schedule.bill_by = 'semester'
	",
	"
		UPDATE
			schedule
		SET
			bill_by = mp.marking_period_id
		FROM
			school_quarters mp
		WHERE
			schedule.start_date BETWEEN mp.start_date AND mp.end_date
			AND schedule.school_id = mp.school_id
			AND schedule.syear = mp.syear
			AND schedule.bill_by = 'quarter'
	",
	"
		UPDATE
			schedule
		SET
			bill_by = mp.marking_period_id
		FROM
			school_progress_periods mp
		WHERE
			schedule.start_date BETWEEN mp.start_date AND mp.end_date
			AND schedule.school_id = mp.school_id
			AND schedule.syear = mp.syear
			AND schedule.bill_by = 'period'
	"
];

foreach($sql as $statement) {
	Database::query($statement);
}

// BEGIN FOCUS-4378
foreach(['course_fees', 'course_fee_groups'] as $table) {
	if(!Database::columnExists($table, 'deleted')) {
		Database::createColumn($table, 'deleted', 'bigint');
	}
}
// END FOCUS-4378

// BEGIN FOCUS-6550
if(!Database::sequenceExists('ps_fa_saig_files_id_seq')) {
	Database::createSequence('ps_fa_saig_files_id_seq');
}

if(!Database::tableExists('ps_fa_saig_files')) {
	// I changed "transfer_at" from TIMESTAMP to DATETIME2 on mssql, which is similar to postgres's TIMESTAMP - Bob M
	$sql = Database::preprocess("
		CREATE TABLE ps_fa_saig_files (
			\"id\" BIGINT PRIMARY KEY DEFAULT {{next:ps_fa_saig_files_id_seq}},
			\"school_id\" BIGINT NULL,
			\"filename\" VARCHAR(255) NOT NULL,
			\"destination\" VARCHAR(7) NULL,
			\"message_class\" VARCHAR(8) NULL,
			\"batch\" VARCHAR(100) NULL,
			\"transfer_at\" {$datetime_type} NULL,
			\"transfer_type\" VARCHAR(1) NULL,
			\"imported\" INT NULL,
			\"deleted\" INT NULL,
			\"content\" {$text_type} NULL
		)
	");

	Database::query($sql);
}

foreach(range(1, 10) as $n) {
	$column = "school_code_{$n}";

	if(!Database::columnExists('ps_fa_isirs', $column)) {
		Database::createColumn('ps_fa_isirs', $column, 'varchar', 8);
	}
}
// END FOCUS-6550

if(!Database::columnExists('school_gradelevels', 'rollover')) {
	Database::createColumn('school_gradelevels', 'rollover', 'varchar', 1);

	Database::query("
		update school_gradelevels set rollover='Y'
	");
}

// BEGIN FOCUS-6665
$courses = [
	[
		'link'     => 'https://training.focusschoolsoftware.com/moodle/course/view.php?id=211',
		'title'    => 'LMS Assignments',
		'modname'  => 'Grades/Grades.php',
		'profiles' => 'teacher'
	],
	[
		'link'     => 'https://training.focusschoolsoftware.com/moodle/course/view.php?id=228',
		'title'    => 'Non-Instructional Core 8.0',
		'modname'  => 'misc/Portal.php',
		'profiles' => 'admin'
	],
	[
		'link'     => 'https://training.focusschoolsoftware.com/moodle/course/view.php?id=241',
		'title'    => 'Teacher Advanced Reports',
		'modname'  => 'misc/Export.php',
		'profiles' => 'teacher'
	],
	[
		'link'     => 'https://training.focusschoolsoftware.com/moodle/course/view.php?id=193',
		'title'    => 'Purchase Order / Request Process',
		'modname'  => 'menu::ap_requests',
		'profiles' => 'ERP'
	],
	[
		'link'     => 'https://training.focusschoolsoftware.com/moodle/course/view.php?id=192',
		'title'    => 'Invoicing',
		'modname'  => 'menu::ap_invoices',
		'profiles' => 'ERP'
	],
	[
		'link'     => 'https://training.focusschoolsoftware.com/moodle/course/view.php?id=191',
		'title'    => 'Checks',
		'modname'  => 'menu::ap_checks',
		'profiles' => 'ERP'
	],
	[
		'link'     => 'https://training.focusschoolsoftware.com/moodle/course/view.php?id=189',
		'title'    => 'Budgeting',
		'modname'  => 'menu::gl_budget_maintenance',
		'profiles' => 'ERP'
	]
];

$check_sql = "
	SELECT
		1
	FROM
		university_courses
	WHERE
		link = :link AND
		title = :title AND
		modname = :modname AND
		profiles = :profiles
";

$insert_sql = Database::preprocess("
	INSERT INTO university_courses (
		{{postgres:id,}}
		link,
		title,
		modname,
		profiles
	)
	VALUES (
		{{postgres:{{next:university_courses_seq}},}}
		:link,
		:title,
		:modname,
		:profiles
	)
");

foreach($courses as $course) {
	$existing = Database::get($check_sql, $course);

	if(empty($existing)) {
		Database::query($insert_sql, $course);
	}
}
// END FOCUS-6665

foreach([2, 3] as $n) {
	if(!Database::columnExists('master_courses', "grad_subject_area{$n}")) {
		Database::createColumn('master_courses', "grad_subject_area{$n}", 'varchar', 5);
	}

	if(!Database::columnExists('courses', "grad_subject_id{$n}")) {
		Database::createColumn('courses', "grad_subject_id{$n}", 'bigint');
	}
}

// BEGIN FOCUS-6714
// These were moved to FOCUS-7224a (8.0.1) since the are required earlier - Bob M
// ALTER TABLE user_audit_trail ADD logged_in_as BIGINT;
// ALTER TABLE database_object_log ADD logged_in_class VARCHAR(255);
// ALTER TABLE database_object_log ADD logged_in_id BIGINT;
// END FOCUS-6714

$existing = Database::getIndexes('grad_subjects');

$new = [
	'grad_subjects_ind1' => 'short_name',
	'grad_subjects_ind2' => 'school_id',
	'grad_subjects_ind3' => 'sort_order',
];

foreach($new as $name => $column) {
	if(!isset($existing[$name])) {
		Database::query("
			CREATE INDEX \"{$name}\" ON grad_subjects ({$column})
		");
	}
}

$existing = Database::getPrimaryKey('grad_subject_credits');

if(empty($existing)) {
	Database::query("
		ALTER TABLE grad_subject_credits
		ADD PRIMARY KEY (id)
	");
}

$existing = Database::getIndexes('grad_subject_credits');

if(!isset($existing['grad_subject_credits_ind1'])) {
	Database::query("
		CREATE INDEX \"grad_subject_credits_ind1\" ON grad_subject_credits (grad_program_id)
	");
}
