<?php
// Block run of MS SQL Server code for now.
if (Database::$type === 'mssql') {
	return false;
}

// Make sure FOCUS-13030 has run so that the bug exists to squash.
Migrations::depend('FOCUS-13030');

// Block run if immunizations isn't installed.
if (!Database::tableExists('imm_config')) {
	return false;
}

Database::query("insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
	(select id from imm_rulesets where title = 'Florida:DTaP:RS1'),
	(select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
	3,
	1970,
	null,
	'N',
	null,
	null,
	'Requires 5 doses.'
);");

Database::query("insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
	(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
	(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
	1
);");

Database::query("insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
	(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
	(select id from imm_dynamic_values where dv_type = 'int' and dv_value = '5'),
	2
);");
