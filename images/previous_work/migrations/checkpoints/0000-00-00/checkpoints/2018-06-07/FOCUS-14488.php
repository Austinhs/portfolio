<?php
if(empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}
Migrations::depend('FOCUS-14076');
Migrations::depend('FOCUS-14244');

Database::begin();

FocusUser::dropViews();

$aliases = [
	'original_position_employment_date',
	'continuous_employment_date',
	'rehire_date',
	'vacation_years',
	'birth_date',
	'marital_status',
	'handicap_condition1',
	'handicap_condition2',
	'handicap_condition3',
	'military_veteran',
	'retiree',
	'medicare_eligible',
	'ethics_training',
	'retirement_date',
	'state_identifier',
	'citizenship_status',
	'citizenship_status_expiration_date',
	'exempt_from_public_record',
	'check_location',
	'bus_route',
	'sick_bank_membership',
	'drop_start_date',
	'drop_end_date',
	'highest_degree_earned',
	'retire_elig_date',
	'state_retirement_system_num',
	'aca_assessment_date',
	'aca_offered',
	'aca_stability_date',
	'aca_status',
	'gender',
	'race_african_american',
	'race_asian',
	'race_hawaiian',
	'race_native',
	'race_white',
	'years_of_experience',
	'regular_service_separation_date',
	'separation_reason',
	'health_insurance_offered',
	'reported_new_hire',
	'w4_status',
	'w4_allowances',
	'w4_additional',
	'w4_opt_out_paper_w2',
	'w4_exempt',
	'highly_qualified_paraprofessional',
	'mentor_supervising_educator',
	'multi_district',
	'multi_district_code',
	'performance_based_pay',
	'prnc_cert_pgm',
	'reading_endorsement_competency1',
	'reading_endorsement_competency2',
	'reading_endorsement_competency3',
	'reading_endorsement_competency4',
	'reading_endorsement_competency5',
	'reading_endorsement_competency6',
	're_comp_2011_1',
	're_comp_2011_2',
	're_comp_2011_3',
	're_comp_2011_4',
	're_comp_2011_5',
	'ell_hours',
	'ell_startdate',
	'ell_ling',
	'ell_test',
	'ell_ccc',
	'ell_methods',
	'ell_curr'
];

foreach ($aliases as $alias) {
	if (!Database::columnExists('users', $alias)) {
		$field = ERPUser::getFieldByAlias($alias);
		$field = new CustomField($field['id']);
		$field->setColumnName($alias);
		$field->setSourceClass('FocusUser');
		$field->persist();
	}
}

$w4 = [
	'w4_allowances',
	'w4_additional'
];

foreach ($w4 as $w4_alias) {
	Database::query("UPDATE CUSTOM_FIELDS SET TYPE = 'numeric' WHERE ALIAS='{$w4_alias}'");
	Database::changeColumnType('users', $w4_alias, 'numeric');
}

CustomFieldObject::clearCache();

FocusUser::refreshViews();

Database::commit();