<?php

// Depend on the 8.2.1 migration
Migrations::depend('FOCUS-7224b');

// Depend on all migrations that were added in 8.2.1
Migrations::depend('FOCUS-6221');
Migrations::depend('FOCUS-6240');
Migrations::depend('FOCUS-6336');
Migrations::depend('FOCUS-6337');
Migrations::depend('FOCUS-6359');

echo "8.2.2 Migration";

// Create GPA View permission for users who already have view access
Database::query("
	INSERT INTO permission(profile_id, \"key\")
	SELECT
		profile_id,
		'SIS::AllowGPAView' AS \"key\"
	FROM
		permission p
	WHERE
		p.\"key\" = 'Grades/StudentRCGrades.php:can_view'
		AND NOT EXISTS (
			SELECT
				''
			FROM
				permission p2
			WHERE
				p2.profile_id = p.profile_id
				AND p2.\"key\" = 'SIS::AllowGPAView'
		)
");

// Add the "discipline_referrals.dismissed" column
if(!Database::columnExists('discipline_referrals', 'dismissed')) {
	Database::createColumn('discipline_referrals', 'dismissed', 'varchar', 1);

	Database::query("
		update discipline_referrals set dismissed='Y' where processed='Y'
	");
}

// BEGIN FOCUS-6188
$student_gpa_calculated_columns = [
	'custom_2_gpa' => 'numeric',
	'custom_3_gpa' => 'numeric',
	'custom_4_gpa' => 'numeric',
	'custom_5_gpa' => 'numeric',

	'custom_2_rank' => 'numeric',
	'custom_3_rank' => 'numeric',
	'custom_4_rank' => 'numeric',
	'custom_5_rank' => 'numeric'
];

foreach($student_gpa_calculated_columns as $column => $type) {
	if(!Database::columnExists('student_gpa_calculated', $column)) {
		Database::createColumn('student_gpa_calculated', $column, $type);
	}
}
// END FOCUS-6188
