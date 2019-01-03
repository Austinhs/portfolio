<?php

if (!Database::tableExists('imm_ruleset_groups_rulesets') || !Database::tableExists('imm_rulesets_rules')){
	return false;
   }

if (Database::$type === 'mssql') {
		return false;
}

//This code adds Tdap to immunizations 
Database::begin();

	Database::query("
		DELETE from imm_vaccines where title = 'Tdap';
	");

	Database::query("
		INSERT INTO imm_immunizations (title,code) SELECT
			'Tdap'
			,   'tdp'
			WHERE NOT EXISTS
			(SELECT '' from imm_immunizations where code = 'tdp')
			;
	");


	Database::query("
		insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) SELECT (select id from imm_immunizations where code = 'tdp'), (select id from imm_ruleset_groups where title = 'Florida'), 'Tdap (Tetanus-Diphtheria-Acellular-Pertussis) vaccine', 'Tdap'
			WHERE NOT EXISTS (SELECT '' from imm_vaccines where code = 'Tdap');
	");

	Database::query("
		insert into imm_rules (code) SELECT 'case when vaccine_class = '':dv1:'' and cast(max(shot_date) as date) > (max(dob) + interval''7 years'') then 1 else 0 end'
			WHERE NOT EXISTS(SELECT '' from imm_rules where code = ('case when vaccine_class = '':dv1:'' and cast(max(shot_date) as date) > (max(dob) + interval''7 years'') then 1 else 0 end'));
	");

	Database::query("
		insert into imm_rulesets (title, compliance_msg, error_msg) SELECT 'Florida:Tdap:RS1', '1 dose after age 7 of DTaP or Tdap', null
			WHERE NOT EXISTS (SELECT '' from imm_rulesets where title = 'Florida:Tdap:RS1');
	");

	Database::query("
		insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) SELECT 
			(select id from imm_ruleset_groups where title = 'Florida'),
			(select id from imm_rulesets where title = 'Florida:Tdap:RS1'),
			(select id from imm_immunizations where title = 'Tdap'),
			10,
			1970,
			null,
			'Tdap compliance complete.',
			'Tdap compliance incomplete.'
				WHERE NOT EXISTS (SELECT '' from imm_ruleset_groups_rulesets where error_msg = 'Tdap compliance incomplete.');
	");

	Database::query("
		insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) SELECT
			(select id from imm_rulesets where title = 'Florida:Tdap:RS1'),
			(select id from imm_rules where code = 'case when vaccine_class = '':dv1:'' and cast(max(shot_date) as date) > (max(dob) + interval''7 years'') then 1 else 0 end'),
			1,
			1970,
			null,
			'N',
			null,
			null,
			'Requires 1 dose after age 7 of DTap or Tdap.'
				WHERE NOT EXISTS (SELECT '' from imm_rulesets_rules where error_msg = 'Requires 1 dose after age 7 of DTap or Tdap.');
	");

	Database::query("
		insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order)
			SELECT
				(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Tdap:RS1') and rule_id = (select id from imm_rules where code = 'case when vaccine_class = '':dv1:'' and cast(max(shot_date) as date) > (max(dob) + interval''7 years'') then 1 else 0 end') and rule_order = 1),
				(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
				1
			WHERE NOT EXISTS
			(SELECT '' from imm_rulesets_rules_dynamic_values
			WHERE
			rulesets_rules_id = (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Tdap:RS1') and rule_id = (select id from imm_rules where code = 'case when vaccine_class = '':dv1:'' and cast(max(shot_date) as date) > (max(dob) + interval''7 years'') then 1 else 0 end') and rule_order = 1)
			AND dynamic_value_id = (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'));
	");

	Database::query("
	UPDATE edit_rules SET SQL = 'select fn_imm_calc(''dtp'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''tdp'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hepa'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hepb'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hib'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''ms'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''men'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''mu'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''pnc'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''pol'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''rub'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''var'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hepb2'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hepb3'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc_clean(coalesce({STUDENT_ID}, 1), {SYEAR});'
		WHERE NAME in ('IMM - Exemption Date','IMM - Exemption Expiration Date','IMM - Vaccination Date 1','IMM - Vaccination Date 2','IMM - Vaccination Date 3','IMM - Vaccination Date 4','IMM - Vaccination Date 5','IMM - Vaccination Date 6','IMM - Vaccination Date 7')
			AND NOT EXISTS (SELECT ''  from edit_rules where SQL = 'select fn_imm_calc(''dtp'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''tdp'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hepa'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hepb'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hib'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''ms'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''men'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''mu'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''pnc'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''pol'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''rub'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''var'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hepb2'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc(''hepb3'', coalesce({STUDENT_ID}, 1), {SYEAR}), fn_imm_calc_clean(coalesce({STUDENT_ID}, 1), {SYEAR});' );
	");

Database::commit();










	
