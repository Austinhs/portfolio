<?php
if (!Database::tableExists('imm_ruleset_groups_rulesets') || !Database::tableExists('imm_rulesets_rules')){
  return false;
 }
 
if (Database::$type === 'postgres') {
	MigrationFOCUS17035::runPostgres();
}
elseif (Database::$type === 'mssql'){
	return false;
}

	class MigrationFOCUS17035 {
		public static function runPostgres() {
			
			Database::begin();
			
			Database::query("
				INSERT INTO IMM_RULES(code) SELECT('CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END')
					WHERE NOT EXISTS
					(SELECT '' from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END')");
			
			Database::query("
				UPDATE IMM_RULESETS 
				SET compliance_msg = '2 doses, 28 days between.'
				WHERE title in ('Florida:Measles:RS1','Florida:Mumps:RS1','Florida:Rubella:RS1')
					AND compliance_msg = '2 doses, 1 on or after 4th birthday.'");
			
			Database::query("
				delete from imm_rulesets_rules_dynamic_values
				where rulesets_rules_id in
				(select x.id
				from imm_vaccines v
				join imm_immunizations i on i.id = v.immunization_id
				join imm_ruleset_groups_rulesets r on r.ruleset_group_id = v.ruleset_group_id and r.immunization_id = i.id
				join imm_rulesets_rules x on x.ruleset_id = r.ruleset_id
				WHERE i.code in ('rub','ms','mu')
				and v.ruleset_group_id = 2
				and x.error_msg = 'Requires 1 dose on or after 4th birthday.')
				");
			
			Database::query("
				DELETE FROM imm_rulesets_rules where id in (
				select x.id
				from imm_vaccines v
				join imm_immunizations i on i.id = v.immunization_id
				join imm_ruleset_groups_rulesets r on r.ruleset_group_id = v.ruleset_group_id and r.immunization_id = i.id
				join imm_rulesets_rules x on x.ruleset_id = r.ruleset_id
				WHERE i.code in ('rub','ms','mu')
				and v.ruleset_group_id = 2
				and x.error_msg = 'Requires 1 dose on or after 4th birthday.'
				)
				");
				
			Database::query("
				INSERT INTO imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg)
				SELECT
					(select id from imm_rulesets where title = 'Florida:Measles:RS1'),
					(select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END'),
					2,
					1970,
					null,
					'N',
					null,
					null,
					'Requires 2 doses of MMR'
				WHERE NOT EXISTS
				(SELECT '' from imm_rulesets_rules
				WHERE
				ruleset_id =  (select id from imm_rulesets where title = 'Florida:Measles:RS1')
				AND rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END'))
			");
				
			Database::query("
				insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order)
				SELECT
					(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END') and rule_order = 2),
					(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
					1
				WHERE NOT EXISTS
				(SELECT '' from imm_rulesets_rules_dynamic_values
				WHERE
				rulesets_rules_id = (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END') and rule_order = 2)
				AND dynamic_value_id = (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'))
			");
				
			Database::query("
				INSERT INTO imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg)
				SELECT
					(select id from imm_rulesets where title = 'Florida:Mumps:RS1'),
					(select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END'),
					2,
					1970,
					null,
					'N',
					null,
					null,
					'Requires 2 doses of MMR'
				WHERE NOT EXISTS
				(SELECT '' from imm_rulesets_rules
				WHERE
				ruleset_id =  (select id from imm_rulesets where title = 'Florida:Mumps:RS1')
				AND rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END'))
			");
				
			Database::query("
				insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order)
				SELECT
					(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END') and rule_order = 2),
					(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
					1
				WHERE NOT EXISTS
				(SELECT '' from imm_rulesets_rules_dynamic_values
				WHERE
				rulesets_rules_id = (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END') and rule_order = 2)
				AND dynamic_value_id = (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'))
			");
				
			Database::query("
				INSERT INTO imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg)
				SELECT
					(select id from imm_rulesets where title = 'Florida:Rubella:RS1'),
					(select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END'),
					2,
					1970,
					null,
					'N',
					null,
					null,
					'Requires 2 doses of MMR'
				WHERE NOT EXISTS
				(SELECT '' from imm_rulesets_rules
				WHERE
				ruleset_id =  (select id from imm_rulesets where title = 'Florida:Rubella:RS1')
				AND rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END'))
			");
				
			Database::query("
				insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order)
				SELECT
					(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END') and rule_order = 2),
					(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
					1
				WHERE NOT EXISTS
				(SELECT '' from imm_rulesets_rules_dynamic_values
				WHERE
				rulesets_rules_id = (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'CASE WHEN vaccine_class = '':dv1:'' AND CAST(max(shot_date) - min(shot_date) as int) >= 28 THEN 1 ELSE 0 END') and rule_order = 2)
				AND dynamic_value_id = (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'))
				
			");
				
			Database::commit();
	}	
	
 }	
