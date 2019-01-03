<?php

if (!Database::tableExists('gl_hr_demographic_old') || empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}

Database::begin();

$fields = [
	[
		'title' => 'Ethnicity (Hispanic or Latino)',
		'column' => 'ethnicity',
		'type' => 'checkbox'
	],
	[
		'title' => 'Highly Qualified Paraprofessional',
		'column' => 'highly_qualified_paraprofessional',
		'type' => 'select'
	],
	[
		'title' => 'Mentor Supervising Educator',
		'column' => 'mentor_supervising_educator',
		'type' => 'select'
	],
	[
		'title' => 'Multidistrict Employee, Assignment Identifier',
		'column' => 'multi_district',
		'type' => 'select'
	],
	[
		'title' => 'Multidistrict Employee, District Number',
		'column' => 'multi_district_code',
		'type' => 'text'
	],
	[
		'title' => 'Performance Pay',
		'column' => 'performance_based_pay',
		'type' => 'select'
	],
	[
		'title' => 'Principal Certification Program',
		'column' => 'prnc_cert_pgm',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 1',
		'column' => 'reading_endorsement_competency1',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 2',
		'column' => 'reading_endorsement_competency2',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 3',
		'column' => 'reading_endorsement_competency3',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 4',
		'column' => 'reading_endorsement_competency4',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 5',
		'column' => 'reading_endorsement_competency5',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 6',
		'column' => 'reading_endorsement_competency6',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 1 (2011)',
		'column' => 're_comp_2011_1',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 2 (2011)',
		'column' => 're_comp_2011_2',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 3 (2011)',
		'column' => 're_comp_2011_3',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 4 (2011)',
		'column' => 're_comp_2011_4',
		'type' => 'select'
	],
	[
		'title' => 'Reading Endorsement Competency 5 (2011)',
		'column' => 're_comp_2011_5',
		'type' => 'select'
	],
	[
		'title' => 'Hours',
		'column' => 'ell_hours',
		'type' => 'numeric'
	],
	[
		'title' => 'Date',
		'column' => 'ell_startdate',
		'type' => 'date'
	],
	[
		'title' => 'Ling',
		'column' => 'ell_ling',
		'type' => 'checkbox'
	],
	[
		'title' => 'Test',
		'column' => 'ell_test',
		'type' => 'checkbox'
	],
	[
		'title' => 'CCC',
		'column' => 'ell_ccc',
		'type' => 'checkbox'
	],
	[
		'title' => 'Methods',
		'column' => 'ell_methods',
		'type' => 'checkbox'
	],
	[
		'title' => 'Curr',
		'column' => 'ell_curr',
		'type' => 'checkbox'
	]
];

foreach ($fields as $field) {
	$field = ERPUser::getFieldByAlias($field['column']);
	$field_id = $field['id'];
	Database::query(
		"INSERT INTO PERMISSION (PROFILE_ID,\"key\")
		SELECT
			PROFILE_ID, 'FocusUser:{$field_id}:can_view' as \"key\"
		FROM PERMISSION p
		WHERE
			\"key\" = 'hr::demographic'
			AND NOT EXISTS(
				SELECT ''
				FROM PERMISSION
				WHERE PROFILE_ID = p.PROFILE_ID AND \"key\" = 'FocusUser:{$field_id}:can_view'
			)"
	);
	Database::query(
		"INSERT INTO PERMISSION (PROFILE_ID,\"key\")
		SELECT
			PROFILE_ID, 'FocusUser:{$field_id}:can_edit' as \"key\"
		FROM PERMISSION p
		WHERE
			\"key\" = 'hr::demographic'
			AND NOT EXISTS(
				SELECT ''
				FROM PERMISSION
				WHERE PROFILE_ID = p.PROFILE_ID AND \"key\" = 'FocusUser:{$field_id}:can_edit'
			)"
	);
}

Database::commit();