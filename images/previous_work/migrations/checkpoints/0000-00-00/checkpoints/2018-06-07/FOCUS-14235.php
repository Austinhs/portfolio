<?php

Database::begin();

$tables = [
	'gl_address',
	'gl_contact',
	'gl_direct_deposit',
	'gl_hr_education',
	'gl_emergency_contacts',
	'gl_hr_exigent_circumstances',
	'gl_hr_fingerprint',
	'gl_hr_licenses',
	'gl_hr_personnel_evaluation',
	'gl_hr_skills',
	'gl_hr_staff_experience'
];

$columns = [
	'gl_address'=>[
		'address1','address2','city','state','zipcode','primary_addr','correspondence','payment','mailing','dont_publish'
	],
	'gl_contact'=>[
		'cont_type','cont_data','dont_publish'
	],
	'gl_direct_deposit'=>[
		'account_number','account_type','amount','bank_id','checkall','priority','status'
	],
	'gl_hr_education'=>[
		'degree_earned','major_id','college_id','institution','year_earned','paid_at'
	],
	'gl_emergency_contacts'=>[
		'contact_last_name','contact_first_name','contact_relationship','phone_number_1','phone_number_2'
	],
	'gl_hr_exigent_circumstances'=>[
		'investigation_date','investigation_description','incident_date','incident_description','outcome_date','outcome_description','status_action','status_action_description'
	],
	'gl_hr_fingerprint'=>[
		'fingerprint_screening_required','fingerprint_date_taken','fingerprint_date_cleared','fingerprint_date_expired'
	],
	'gl_hr_licenses'=>[
		'license_type','license_number','license_date_acquired','license_date_expires'
	],
	'gl_hr_personnel_evaluation'=>[
		'fyear','final','eval_rating'
	],
	'gl_hr_skills'=>[
		'skills_date','skills_code'
	],
	'gl_hr_staff_experience'=>[
		'experience_type','previous','experience_current','experience_length','increment_override'
	]
];


foreach ($tables as $table) {
	Database::query(
		"INSERT INTO PERMISSION (PROFILE_ID,\"key\")
		SELECT
			PROFILE_ID, 'FocusUser:form:{$table}:can_create' as \"key\"
		FROM PERMISSION p
		WHERE
			\"key\" = 'FocusUser:form:{$table}:can_create'
			AND NOT EXISTS(
				SELECT ''
				FROM PERMISSION
				WHERE PROFILE_ID = p.PROFILE_ID AND \"key\" = 'FocusUser:form:{$table}:can_create'
			)"
	);
	Database::query(
		"INSERT INTO PERMISSION (PROFILE_ID,\"key\")
		SELECT
			PROFILE_ID, 'FocusUser:form:{$table}:can_delete' as \"key\"
		FROM PERMISSION p
		WHERE
			\"key\" = 'FocusUser:form:{$table}:can_delete'
			AND NOT EXISTS(
				SELECT ''
				FROM PERMISSION
				WHERE PROFILE_ID = p.PROFILE_ID AND \"key\" = 'FocusUser:form:{$table}:can_delete'
			)"
	);
	foreach ($columns[$table] as $column) {
		Database::query(
			"INSERT INTO PERMISSION (PROFILE_ID,\"key\")
			SELECT
				PROFILE_ID, 'FocusUser:form:{$table}:can_delete' as \"key\"
			FROM PERMISSION p
			WHERE
				\"key\" = 'FocusUser:{$table}|{$column}:can_view'
				AND NOT EXISTS(
					SELECT ''
					FROM PERMISSION
					WHERE PROFILE_ID = p.PROFILE_ID AND \"key\" = 'FocusUser:{$table}|{$column}:can_view'
				)"
		);
		Database::query(
			"INSERT INTO PERMISSION (PROFILE_ID,\"key\")
			SELECT
				PROFILE_ID, 'FocusUser:form:{$table}:can_delete' as \"key\"
			FROM PERMISSION p
			WHERE
				\"key\" = 'FocusUser:{$table}|{$column}:can_edit'
				AND NOT EXISTS(
					SELECT ''
					FROM PERMISSION
					WHERE PROFILE_ID = p.PROFILE_ID AND \"key\" = 'FocusUser:{$table}|{$column}:can_edit'
				)"
		);
	}
}

Database::commit();