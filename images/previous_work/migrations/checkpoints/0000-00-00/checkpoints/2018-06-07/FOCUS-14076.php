<?php

define('UserClass','FocusUser');

if (Database::tableExists('gl_hr_demographic_old') || !Database::tableExists('GL_FACILITIES') || empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}

Database::begin();

//Data for fields
$demo_fields = [
	[
		'title' => 'Original Hire Date',
		'column' => 'original_position_employment_date',
		'type' => 'date'
	],
	[
		'title' => 'Continuous Employment Date',
		'column' => 'continuous_employment_date',
		'type' => 'date'
	],
	[
		'title' => 'Rehire Date',
		'column' => 'rehire_date',
		'type' => 'date'
	],
	[
		'title' => 'Vacation Years',
		'column' => 'vacation_years',
		'type' => 'text'
	],
	[
		'title' => 'Birth Date',
		'column' => 'birth_date',
		'type' => 'date',
		'new' => true
	],
	[
		'title' => 'Marital Status',
		'column' => 'marital_status',
		'type' => 'select',
		'new' => true
	],
	[
		'title' => 'Handicap Condition 1',
		'column' => 'handicap_condition1',
		'type' => 'select',
		'new' => true
	],
	[
		'title' => 'Handicap Condition 2',
		'column' => 'handicap_condition2',
		'type' => 'select',
		'new' => true
	],
	[
		'title' => 'Handicap Condition 3',
		'column' => 'handicap_condition3',
		'type' => 'select',
		'new' => true
	],
	[
		'title' => 'Military Veteran',
		'column' => 'military_veteran',
		'type' => 'checkbox',
		'new' => true
	],
	[
		'title' => 'Retiree',
		'column' => 'retiree',
		'type' => 'checkbox'
	],
	[
		'title' => 'Medicare Eligible',
		'column' => 'medicare_eligible',
		'type' => 'checkbox',
		'new' => true
	],
	[
		'title' => 'Ethics Training',
		'column' => 'ethics_training',
		'type' => 'checkbox',
		'new' => true
	],
	[
		'title' => 'Retirement Date',
		'column' => 'retirement_date',
		'type' => 'date'
	],
	[
		'title' => 'State Identifier',
		'column' => 'state_identifier',
		'type' => 'text',
		'new' => true
	],
	[
		'title' => 'Citizenship Status',
		'column' => 'citizenship_status',
		'type' => 'select',
		'new' => true
	],
	[
		'title' => 'Citizenship Status Expiration Date',
		'column' => 'citizenship_status_expiration_date',
		'type' => 'date',
		'new' => true
	],
	[
		'title' => 'Exempt From Public Record',
		'column' => 'exempt_from_public_record',
		'type' => 'checkbox',
		'new' => true
	],
	[
		'title' => 'Check Location',
		'column' => 'check_location',
		'type' => 'select',
		'option_query' => 'SELECT ID, CODE'.CCAT.'\' \''.CCAT.'NAME as LABEL FROM GL_FACILITIES;'
	],
	[
		'title' => 'Bus Route',
		'column' => 'bus_route',
		'type' => 'text'
	],
	[
		'title' => 'Sick Bank Membership',
		'column' => 'sick_bank_membership',
		'type' => 'text',
		'new' => true
	],
	[
		'title' => 'Drop Beginning Date',
		'column' => 'drop_start_date',
		'type' => 'date'
	],
	[
		'title' => 'Drop Ending Date',
		'column' => 'drop_end_date',
		'type' => 'date'
	],
	[
		'title' => 'Highest Degree Earned',
		'column' => 'highest_degree_earned',
		'type' => 'select',
		'new' => true
	],
	[
		'title' => 'Retirement Eligibility Date',
		'column' => 'retire_elig_date',
		'type' => 'date'
	],
	[
		'title' => 'State Retirement Sys Num',
		'column' => 'state_retirement_system_num',
		'type' => 'text'
	],
	[
		'title' => 'ACA Assessment Date',
		'column' => 'aca_assessment_date',
		'type' => 'date'
	],
	[
		'title' => 'ACA Offered',
		'column' => 'aca_offered',
		'type' => 'checkbox'
	],
	[
		'title' => 'ACA Stability Date',
		'column' => 'aca_stability_date',
		'type' => 'date'
	],
	[
		'title' => 'ACA Status',
		'column' => 'aca_status',
		'type' => 'select'
	],
	[
		'title' => 'Gender',
		'column' => 'gender',
		'type' => 'select',
		'new' => true
	],
	[
		'title' => 'Race: African American',
		'column' => 'race_african_american',
		'type' => 'checkbox'
	],
	[
		'title' => 'Race: Asian',
		'column' => 'race_asian',
		'type' => 'checkbox'
	],
	[
		'title' => 'Race: Hawaiian',
		'column' => 'race_hawaiian',
		'type' => 'checkbox'
	],
	[
		'title' => 'Race: Native American',
		'column' => 'race_native',
		'type' => 'checkbox'
	],
	[
		'title' => 'Race: White',
		'column' => 'race_white',
		'type' => 'checkbox'
	],
	[
		'title' => 'Years of Experience',
		'column' => 'years_of_experience',
		'type' => 'numeric'
	],
	[
		'title' => 'Regular Service Separation Date',
		'column' => 'regular_service_separation_date',
		'type' => 'date'
	],
	[
		'title' => 'Separation Reason',
		'column' => 'separation_reason',
		'type' => 'select'
	],
	[
		'title' => 'Health Insurance Offered',
		'column' => 'health_insurance_offered',
		'type' => 'checkbox'
	],
	[
		'title' => 'Reported New Hire',
		'column' => 'reported_new_hire',
		'type' => 'checkbox'
	],
];

$w4_fields = [
	[
		'title' => 'W4 Status',
		'column' => 'w4_status',
		'type' => 'select',
		'new' => true
	],
	[
		'title' => 'W4 Allowances',
		'column' => 'w4_allowances',
		'type' => 'text'
	],
	[
		'title' => 'W4 Additional',
		'column' => 'w4_additional',
		'type' => 'text',
		'new' => true
	],
	[
		'title' => 'Opt Out of Paper W2',
		'column' => 'w4_opt_out_paper_w2',
		'type' => 'checkbox',
		'new' => true
	],
	[
		'title' => 'W4 Exempt',
		'column' => 'w4_exempt',
		'type' => 'checkbox'
	],
];

//Select Option for fields
$select_options = [
	'marital_status' => [
		'M' => 'Married',
		'S' => 'Single',
		'D' => 'Divorced',
		'L' => 'Legally Separated',
		'W' => 'Widowed',
		'X' => 'Unknown'
	],
	'highest_degree_earned' => [
		'P' => 'Paraprofessional',
		'A' => 'Associates',
		'B' => 'Bachelors',
		'C' => 'Certificate',
		'D' => 'Doctorate',
		'H' => 'Child Development Associate (CDA) or CDA Equivalent',
		'M' => 'Masters',
		'S' => 'Specialist',
		'Z' => 'Not Applicable'
	],
	'handicap_condition' => [
		'P' => 'Physically Impaired',
		'V' => 'Visually Impaired',
		'S' => 'Speech Impaired',
		'H' => 'Hearing Impaired',
		'O' => 'Other Health Impaired'
	],
	'citizenship_status' => [
		'A' => 'Alien',
		'P' => 'Permanent Resident',
		'N' => 'Non-citizen National',
		'C' => 'Citizen'
	],
	'w4_status' => [
		'M' => 'Married',
		'S' => 'Single',
		'E' => 'Exempt'
	],
	'aca_status' => [
		'A' => 'In Assessment',
		'S' => 'In Stability',
		'M' => 'In Standard Measurement'
	],
	'gender' => [
		'M' => 'Male',
		'F' => 'Female'
	]
];

//Add Categories
$erp_cat = new CustomFieldCategory();
$erp_cat->setTitle('Employee Demographic');
$erp_cat->setErp(1);
$erp_cat->setSourceClass('FocusUser');
$erp_cat->setSortOrder(8675309);
$erp_cat->persist();
$erp_cat->fixSortOrders();

//Add Category
$w4_cat = new CustomFieldCategory();
$w4_cat->setTitle('W4 Information');
$w4_cat->setErp(1);
$w4_cat->setSourceClass('FocusUser');
$w4_cat->setSortOrder(8675309);
$w4_cat->persist();
$w4_cat->fixSortOrders();

//Get Category info
$erp_cat_id = $erp_cat->getId();
$w4_cat_id = $w4_cat->getId();

$access_profiles = Database::get("SELECT PROFILE_ID FROM PERMISSION WHERE \"key\" = 'hr::demographic'");

Permissions::checkAndInsert($access_profiles, [
	'menu::employee',
	'menu::jobs',
	'menu::payhistory',
	'menu::deductions',
	'menu::retiree_benefits',
	'menu::retiree_payments',
	'menu::files',
	'menu::add_employee'
]);

$sort_increment = 0;
foreach ($demo_fields as $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');
		if (!empty($field['option_query'])) {
			$cf->setOptionQuery($field['option_query']);
		}
		if (!empty($field['new'])) {
			$cf->setNewRecord(1);
		}
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();
		$field_column = $cf->getColumnName();

		if ($field['column'] == 'birth_date') {
			$birth_date_col = $field_column;
		} elseif ($field['column'] == 'original_position_employment_date') {
			$original_position_employment_date_col = $field_column;
		} elseif ($field['column'] == 'continuous_employment_date') {
			$continuous_employment_date_col = $field_column;
		}

		Permissions::checkAndInsert($access_profiles, [
			"FocusUser:{$field_id}:can_view",
			"FocusUser:{$field_id}:can_edit"
		]);

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($erp_cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(8675309+$sort_increment);
		$cfjc->persist();

		$sort_increment++;

		if (substr($field['column'], 0, -1) == 'handicap_condition') {
			$lookup_key = 'handicap_condition';
		} else {
			$lookup_key = $field['column'];
		}

		//Add Select Options & Update Users
		if (Database::tableExists('gl_hr_demographic') && Database::columnExists('gl_hr_demographic', $field['column'])) {
			if ($field['type'] == 'select') {
				foreach($select_options[$lookup_key] as $option_code => $option_label) {
					//Add select option
					$cfso = new CustomFieldSelectOption();
					$cfso->setCode($option_code);
					$cfso->setLabel($option_label);
					$cfso->setSourceId($field_id);
					$cfso->setSourceClass('CustomField');
					$cfso->persist();

					//Get select option info
					$option_id = $cfso->getId();

					//Update
					Database::query(
						"UPDATE USERS
						SET {$field_column}={$option_id}
						WHERE STAFF_ID IN (SELECT STAFF_ID FROM GL_HR_DEMOGRAPHIC WHERE {$field['column']}='{$option_code}')"
					);
				}
			} else {
				//Update
				Database::query(
					"UPDATE USERS
					SET {$field_column} = GL_HR_DEMOGRAPHIC.{$field['column']}
					FROM GL_HR_DEMOGRAPHIC
					WHERE GL_HR_DEMOGRAPHIC.STAFF_ID = USERS.STAFF_ID AND GL_HR_DEMOGRAPHIC.{$field['column']} IS NOT NULL"
				);
			}
		}
	}
}
foreach ($w4_fields as $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');
		if (!empty($field['option_query'])) {
			$cf->setOptionQuery($field['option_query']);
		}
		if (!empty($field['new'])) {
			$cf->setNewRecord(1);
		}
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();
		$field_column = $cf->getColumnName();

		Permissions::checkAndInsert($access_profiles, [
			"FocusUser:{$field_id}:can_view",
			"FocusUser:{$field_id}:can_edit"
		]);

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($w4_cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(8675309+$sort_increment);
		$cfjc->persist();

		$sort_increment++;

		if (substr($field['column'], 0, -1) == 'handicap_condition') {
			$lookup_key = 'handicap_condition';
		} else {
			$lookup_key = $field['column'];
		}

		//Add Select Options & Update Users
		if (Database::tableExists('gl_hr_demographic') && Database::columnExists('gl_hr_demographic', $field['column'])) {
			if ($field['type'] == 'select') {
				foreach ($select_options[$lookup_key] as $option_code => $option_label) {
					//Add select option
					$cfso = new CustomFieldSelectOption();
					$cfso->setCode($option_code);
					$cfso->setLabel($option_label);
					$cfso->setSourceId($field_id);
					$cfso->setSourceClass('CustomField');
					$cfso->persist();

					//Get select option info
					$option_id = $cfso->getId();

					//Update
					Database::query(
						"UPDATE USERS
						SET {$field_column}={$option_id}
						WHERE STAFF_ID IN (SELECT STAFF_ID FROM GL_HR_DEMOGRAPHIC WHERE {$field['column']}='{$option_code}')"
					);
				}
			} else {
				//Update
				Database::query(
					"UPDATE USERS
					SET {$field_column} = GL_HR_DEMOGRAPHIC.{$field['column']}
					FROM GL_HR_DEMOGRAPHIC
					WHERE GL_HR_DEMOGRAPHIC.STAFF_ID = USERS.STAFF_ID AND GL_HR_DEMOGRAPHIC.{$field['column']} IS NOT NULL"
				);
			}
		}
	}
}
$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();

$new_on_add = "('ssn','custom_100000002','custom_607','custom_200000003','email','teacher_certifications','custom_20120004')";
Database::query("UPDATE CUSTOM_FIELDS SET NEW_RECORD = 1 WHERE ALIAS IN {$new_on_add}");
if (Database::tableExists('gl_hr_demographic')) {
	Database::renameTable('gl_hr_demographic', 'gl_hr_demographic_old');
	if (empty($birth_date_col)) {
		$birth_date_col = FocusUser::getFieldByAlias('birth_date');
		$birth_date_col = $birth_date_col['column_name'];
	}
	if (empty($original_position_employment_date_col)) {
		$original_position_employment_date_col = FocusUser::getFieldByAlias('original_position_employment_date');
		$original_position_employment_date_col = $original_position_employment_date_col['column_name'];
	}
	if (empty($continuous_employment_date_col)) {
		$continuous_employment_date_col = FocusUser::getFieldByAlias('continuous_employment_date');
		$continuous_employment_date_col = $continuous_employment_date_col['column_name'];
	}
	Database::query(
		"CREATE VIEW gl_hr_demographic AS
		SELECT DISTINCT
			STAFF_ID,
			FIRST_NAME,
			LAST_NAME,
			MIDDLE_NAME,
			NAME_SUFFIX as GENERATION,
			{$birth_date_col} as BIRTH_DATE,
			{$original_position_employment_date_col} as ORIGINAL_POSITION_EMPLOYMENT_DATE,
			{$continuous_employment_date_col} as CONTINUOUS_EMPLOYMENT_DATE,
			NULL AS DELETED
		FROM USERS u WHERE u.EIN IS NOT NULL"
	);
}

$title_changes = [
	[
		'alias' => 'race_african_american',
		'title' => 'Race: Black or African American'
	],
	[
		'alias' => 'race_hawaiian',
		'title' => 'Race: Native Hawaiian or Other Pacific Islander'
	],
	[
		'alias' => 'race_native',
		'title' => 'Race: American Indian or Alaska Native'
	],
	[
		'alias' => 'race_white',
		'title' => 'Race: White or Caucasian'
	],
];

foreach ($title_changes as $title_change) {
	Database::query("UPDATE CUSTOM_FIELDS SET TITLE = '{$title_change['title']}' WHERE ALIAS = '{$title_change['alias']}'");
}

$demo_cat = Database::get("SELECT ID FROM CUSTOM_FIELD_CATEGORIES WHERE TITLE = 'Employee Demographic'");
$demo_cat_id = $demo_cat[0]['ID'];
$demo_fields = [
	[
		'title' => 'Ethnicity (Hispanic or Latino)',
		'column' => 'ethnicity',
		'type' => 'checkbox'
	]
];

foreach ($demo_fields as $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();
		$field_column = $cf->getColumnName();

		if (Database::tableExists('gl_hr_demographic_old') && Database::columnExists('gl_hr_demographic_old', $field['column'])) {
			Database::query(
				"UPDATE USERS
				SET {$field_column} = GL_HR_DEMOGRAPHIC_OLD.{$field['column']}
				FROM GL_HR_DEMOGRAPHIC_OLD
				WHERE GL_HR_DEMOGRAPHIC_OLD.STAFF_ID = USERS.STAFF_ID AND GL_HR_DEMOGRAPHIC_OLD.{$field['column']} IS NOT NULL"
			);
		}

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($demo_cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(32);
		$cfjc->persist();
	}
}

$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();

//Add Category
$sr_cat = new CustomFieldCategory();
$sr_cat->setTitle('State Reporting');
$sr_cat->setErp(1);
$sr_cat->setSourceClass('FocusUser');
$sr_cat->setSortOrder(8675309);
$sr_cat->persist();
$sr_cat->fixSortOrders();

$sr_cat_id = $sr_cat->getId();

$sr_fields = [
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
];

$select_options = [
	'exempt_public_law' => [
		'Y' => 'Employee or spouse of an employee who is exempt from the Public Records Law',
		'Z' => 'Not applicable'
	],
	'highly_qualified_paraprofessional' => [
		'A' => 'Has an associate\'s or higher degree',
		'B' => 'Has two years of study at an institution of higher education',
		'C' => 'Meets locally approved academic assessment of qualifications',
		'D' => 'Not NCLB qualified',
		'Z' => 'Not Applicable'
	],
	'mentor_supervising_educator' => [
		'Y' => 'Yes',
		'N' => 'No'
	],
	'multi_district' => [
		'X' => 'Multidistrict consortium employee',
		'Y' => 'Employed in more than one district through another formal agreement or employed in multidistrict projects'
	],
	'performance_based_pay' => [
		'Y' => 'Yes',
		'N' => 'No'
	],
	'prnc_cert_pgm' => [
		'A' => 'Admitted (entered the program and not withdrawn during the fiscal year)',
		'B' => 'Enrolled (previously entered, still in the program, and not completed or withdrawn)',
		'C' => 'Completed (completed the program this year)',
		'D' => 'Withdrawn (exited the program without completing the program)',
		'Z' => 'Not applicable/none of the above'
	],
	'reading_endorsement_competency' => [
		'C' => 'C-The instructional staff member is in the process of completing CAR-PD or has completed CAR-PD in its entirety.',
		'G' => 'G-The instructional staff member is in the process of completing NGCAR-PD or has completed NGCAR-PD in its entirety.',
		'N' => 'N-No, the instructional staff member did not complete Competency.',
		'P' => 'P-The instructional staff member is currently working toward completion of K-12 Reading certification.',
		'R' => 'R-The instructional staff member has met the requirement through K-12 Reading certification.',
		'Y' => 'Y-Yes, the instructional staff member completed Competency.',
		'Z' => 'Z-Not applicable - not an instructional employee or not required for applicable to this instructional staff member'
	]
];

$sort_increment = 0;
foreach ($sr_fields as $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');
		if (!empty($field['option_query'])) {
			$cf->setOptionQuery($field['option_query']);
		}
		if (!empty($field['new'])) {
			$cf->setNewRecord(1);
		}
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();
		$field_column = $cf->getColumnName();

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($sr_cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(8675309+$sort_increment);
		$cfjc->persist();

		$sort_increment++;

		if (substr($field['column'], 0, -1) == 'reading_endorsement_competency' || substr($field['column'], 0, -2) == 're_comp_2011') {
			$lookup_key = 'reading_endorsement_competency';
		} else {
			$lookup_key = $field['column'];
		}

		if (Database::tableExists('gl_hr_state_reporting') && Database::columnExists('gl_hr_state_reporting', $field['column'])) {
			//Add Select Options & Update Users
			if ($field['type'] == 'select') {
				foreach ($select_options[$lookup_key] as $option_code => $option_label) {
					//Add select option
					$cfso = new CustomFieldSelectOption();
					$cfso->setCode($option_code);
					$cfso->setLabel($option_label);
					$cfso->setSourceId($field_id);
					$cfso->setSourceClass('CustomField');
					$cfso->persist();

					//Get select option info
					$option_id = $cfso->getId();

					//Update
					Database::query(
						"UPDATE USERS
						SET {$field_column}={$option_id}
						WHERE STAFF_ID IN (SELECT STAFF_ID FROM gl_hr_state_reporting WHERE {$field['column']}='{$option_code}')"
					);
				}
			} else {
				//Update
				Database::query(
					"UPDATE USERS
					SET {$field_column} = gl_hr_state_reporting.{$field['column']}
					FROM gl_hr_state_reporting
					WHERE gl_hr_state_reporting.STAFF_ID = USERS.STAFF_ID AND gl_hr_state_reporting.{$field['column']} IS NOT NULL"
				);
			}
		}
	}
}
$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();

//Add Category
$ell_cat = new CustomFieldCategory();
$ell_cat->setTitle('ELL');
$ell_cat->setErp(1);
$ell_cat->setSourceClass('FocusUser');
$ell_cat->setSortOrder(8675309);
$ell_cat->persist();
$ell_cat->fixSortOrders();

$ell_cat_id = $ell_cat->getId();

$ell_fields = [
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

$sort_increment = 0;
foreach ($ell_fields as $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();
		$field_column = $cf->getColumnName();

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($ell_cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(8675309+$sort_increment);
		$cfjc->persist();

		$sort_increment++;

		//Update
		if (Database::tableExists('gl_hr_education') && Database::columnExists('gl_hr_education', $field['column'])) {
			Database::query(
				"UPDATE USERS
				SET {$field_column} = gl_hr_education.{$field['column']}
				FROM gl_hr_education
				WHERE gl_hr_education.STAFF_ID = USERS.STAFF_ID AND gl_hr_education.{$field['column']} IS NOT NULL"
			);
		}
	}
}
$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();


Database::commit();
