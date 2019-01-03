<?php
Database::begin();

Migrations::depend('FOCUS-11459');

//Deleting school_id column from grad_subjects
$tablesWithGradSubject = ['student_report_card_grades', 'grad_subject_credits', 'courses', 'grad_year_requirements'];

foreach ($tablesWithGradSubject as $tableToUpdate) {

	$grad_subject_id_column = 'grad_subject_id';

	if($tableToUpdate == 'grad_year_requirements'){
		$grad_subject_id_column = 'grad_subject';
	}

	Database::query("
						WITH get_grad_subjects_by_max_id AS (
							SELECT
								MAX(id) AS id,
								short_name
							FROM
								grad_subjects
							GROUP BY
								short_name
						),
						get_all_gradsubjects AS (
							SELECT
								DISTINCT " . $grad_subject_id_column . " AS gsid,
								(
									SELECT
										short_name
									FROM
										grad_subjects gs
									WHERE
										gs.id = srcg." . $grad_subject_id_column . "
								) short_name
							FROM
								" . $tableToUpdate . " srcg
							WHERE
								" . $grad_subject_id_column . " IS NOT NULL
						),
						get_correct_gradsubject_id AS (
							SELECT
								gs.gsid,
								gsm.id AS new_id
							FROM
								get_all_gradsubjects gs
								JOIN get_grad_subjects_by_max_id gsm ON (gsm.short_name = gs.short_name)
						)
						UPDATE
							" . $tableToUpdate . "
						SET
							" . $grad_subject_id_column . " = c.new_id
						FROM
							get_correct_gradsubject_id c
						WHERE
							1 = 1
							AND c.gsid = " . $grad_subject_id_column . ";

					");
}

if(Database::$type  === 'postgres'){
	Database::query("
		DELETE FROM
			grad_subjects t1
		WHERE
			1 = 1
			AND EXISTS (
				SELECT
					''
				FROM
					grad_subjects t2
				WHERE
					1 = 1
					AND t2.short_name = t1.short_name
					AND t2.id > t1.id
			);
		");

	Database::query("
			ALTER TABLE
				grad_subjects
			DROP
				school_id;
			");
}
else {
	//MSSQL
	Database::query("
		DELETE FROM
			grad_subjects
		WHERE id IN
			(
				SELECT DISTINCT
					t1.id
				FROM
					grad_subjects t1 INNER JOIN
					grad_subjects t2 ON (t2.short_name = t1.short_name)
				WHERE
					1 = 1
					AND t1.id < t2.id
			);
		");

	//Delete the indexes first
	//IX_SchoolID_ID index is for id and school_id columns
	$indexes = Database::getIndexes('grad_subjects', null, false);
	foreach ($indexes as $row) {

		$column = $row['COLUMN_NAME'];
		$index  = $row['INDEX_NAME'];

		if($column == 'school_id'){

			//Look if there is another column using the same index
			$column_names = [];
			foreach ($indexes as $rows) {
				if($rows['INDEX_NAME'] == $index && $rows['COLUMN_NAME'] !== 'school_id'){
					$column_names[] = $rows['COLUMN_NAME'];
				}
			}

			//Delete the index
			Database::query("
				DROP INDEX {$index} ON grad_subjects;
			");

			//Re-create the index excluding school_id
			if(!empty($column_names)){
				$names = join(', ', $column_names);
				Database::query("
					CREATE INDEX {$index} ON grad_subjects ({$names});
				");
			}
		}
	}

	//Delete the column
	Database::query("
		ALTER TABLE
			grad_subjects
		DROP
			COLUMN school_id;
	");
}

//Creating new tables
if (!Database::tableExists('grad_requirements_category')) {

	if(!Database::sequenceExists('grad_requirements_category_seq')){
		Database::createSequence('grad_requirements_category_seq');
	}

	$sql = Database::preprocess("
		CREATE TABLE grad_requirements_category (
			id BIGINT PRIMARY KEY DEFAULT {{next:grad_requirements_category_seq}},
			title varchar(255) NULL,
			template varchar(255) NULL,
			class_name varchar(255) NULL
		)
	");

	Database::query($sql);

	$sql = Database::preprocess( "
					INSERT INTO grad_requirements_category (
						{{postgres:ID,}} TITLE, TEMPLATE, CLASS_NAME)
					VALUES
					({{postgres:{{next:grad_requirements_category_seq}},}}'Main', 'main_category', 'MainCategoryGraduationRequirementsReport'),
					({{postgres:{{next:grad_requirements_category_seq}},}}'Merit Designation Requirements', 'merit', 'MeritCategoryGradRequirementsReport'),
					({{postgres:{{next:grad_requirements_category_seq}},}}'Scholar Designation Requirements', 'scholar', 'ScholarCategoryGradRequirementsReport')"
			);

	Database::query($sql);
}

if (!Database::tableExists('grad_requirements')) {

	if(!Database::sequenceExists('grad_requirements_seq')){
		Database::createSequence('grad_requirements_seq');
	}

	$sql = Database::preprocess("
		CREATE TABLE grad_requirements (
			id BIGINT PRIMARY KEY DEFAULT {{next:grad_requirements_seq}},
			title varchar(255) NULL,
			\"rule\" varchar(255) NULL,
			grad_requirements_category_id int NULL
		)
	");

	Database::query($sql);

	$grad_requirements_category_id = "(select id from grad_requirements_category where template = 'scholar')";

	$sql = Database::preprocess("
			INSERT INTO grad_requirements
				({{postgres:ID,}} title, \"rule\", grad_requirements_category_id)
			VALUES
			({{postgres:{{next:grad_requirements_seq}},}} 'Earned 1 Credit in Statistics or Equally rigorous Course', 'EarnedOneCreditStatisticsOrEquallyRigorousCourse', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Passed Biology EOC', 'PassedBiologyEOC', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Earned 1 Credit in Chemistry/Physics', 'EarnedOneCreditChemistryPhysics', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Earned 1 Credit in Course Equally rigorous to Chemistry/Physics', 'EarnedOneCreditCourseEquallyRogorousChemistryPhysics', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Passed U.S. History EOC', 'PassedUSHistoryEOC', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Earned 2 credits in same foreign language', 'EarnedTwoCreditsForeignLanguage', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Earned 1 credit in AP/IB/AICE/Dual Enrollment', 'EarnedOneCreditApIbAiceDualEnrollment', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Earned 1 credit in Algebra II', 'EarnedOneCreditAlgebraII', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Passed Geometry EOC', 'PassedGeometryEOC', {$grad_requirements_category_id}),

			({{postgres:{{next:grad_requirements_seq}},}} 'Passed Algebra II EOC', 'PassedAlgebraIIEOC', {$grad_requirements_category_id})
	");

	Database::query($sql);

	$grad_requirements_category_id = "(select id from grad_requirements_category where template = 'merit')";

	$sql = Database::preprocess("INSERT INTO grad_requirements (
		{{postgres:ID,}} title, \"rule\", grad_requirements_category_id)
		VALUES
		({{postgres:{{next:grad_requirements_seq}},}} 'Earned Industry Certification', 'EarnedIndustryCertification', {$grad_requirements_category_id})
	");

	Database::query($sql);
}

//Checking all custom_fields exists
$fields = [
	[
		"column_name" => "custom_200000210",
		"alias"       => "civics_eoc_date",
		"title"       => "Civics EOC Pass Date",
		"type"        => "date",
		"legacy_id"   => 200000210,
	],
	[
		"column_name" => "custom_2012000016",
		"alias"       => "geometry_eoc_date",
		"title"       => "Geometry EOC Pass Date",
		"type"        => "date",
		"legacy_id"   => 2012000016,
	],
	[
		"column_name" => "custom_197",
		"alias"       => "fcat_math_pass",
		"title"       => "FCAT Math Pass",
		"type"        => "text",
		"legacy_id"   => 197,
	],
	[
		"column_name" => "custom_2012000015",
		"alias"       => "biology_i_eoc_date",
		"title"       => "Biology I EOC Pass Date",
		"type"        => "date",
		"legacy_id"   => 2012000015,
	],
	[
		"column_name" => "custom_2012000014",
		"alias"       => "algebra_i_eoc_date",
		"title"       => "Algebra I EOC Pass Date",
		"type"        => "date",
		"legacy_id"   => 2012000014,
	],
	[
		"column_name" => "custom_196",
		"alias"       => "reading_pass_date",
		"title"       => "Graduation Reading Requirement Pass Date",
		"type"        => "text",
		"legacy_id"   => 196,
	],
	[
		"column_name" => "custom_2012000018",
		"alias"       => "ushistory_eoc_date",
		"title"       => "US History EOC Pass Date",
		"type"        => "date",
		"legacy_id"   => 2012000018,
	]
];

foreach($fields as $field) {
	$tmp_field = SISStudent::getFieldByColumnName($field['column_name']);

	if(empty($tmp_field)) {
		$obj = new CustomField();

		$obj
			->setSourceClass('SISStudent')
			->setRecord($field);
	}
	else {
		$obj = new CustomField($tmp_field['id']);
	}

	$obj
		->setSystem(1)
		->setAlias($field['alias'])
		->persist();
}

SISStudent::refreshViews();

// Change permission from Grades/PrintGradReport.php to Grades/PrintGraduationRequirementsReport.php
$gr_query = "
	UPDATE
		permission
	SET
		\"key\" = REPLACE(\"key\", 'Grades/PrintGradReport.php', 'Grades/PrintGraduationRequirementsReport.php')
	WHERE
		\"key\" LIKE 'Grades/PrintGradReport.php%' AND
		NOT EXISTS(
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = permission.profile_id AND
				p2.\"key\" = REPLACE(permission.\"key\", 'Grades/PrintGradReport.php', 'Grades/PrintGraduationRequirementsReport.php')
		)
";

// Copy permission from Grades/PrintGraduationRequirementsReport.php to Grades/PrintProgressionPlans.php
$pp_query = "
	INSERT INTO permission (
		profile_id,
		\"key\"
	)
	SELECT
		profile_id,
		REPLACE(permission.\"key\", 'Grades/PrintGraduationRequirementsReport.php', 'Grades/PrintProgressionPlans.php') AS \"key\"
	FROM
		permission
	WHERE
		\"key\" LIKE 'Grades/PrintGraduationRequirementsReport.php%' AND
		NOT EXISTS(
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = permission.profile_id AND
				p2.\"key\" = REPLACE(permission.\"key\", 'Grades/PrintGraduationRequirementsReport.php', 'Grades/PrintProgressionPlans.php')
		)
";

Database::query($gr_query);
Database::query($pp_query);


Database::commit();

//------------------


// if (!Database::tableExists('grad_requirements_waivers')) {

// 	if(!Database::sequenceExists('grad_requirements_waivers_seq')){
// 		Database::createSequence('grad_requirements_waivers_seq');
// 	}

// 	$sql = Database::preprocess("
// 		CREATE TABLE grad_requirements_waivers (
// 			id BIGINT PRIMARY KEY DEFAULT {{next:grad_requirements_waivers_seq}},
// 			title varchar(255) NULL,
// 			rule varchar(255) NULL
// 		)
// 	");

// 	Database::query($sql);
// }
