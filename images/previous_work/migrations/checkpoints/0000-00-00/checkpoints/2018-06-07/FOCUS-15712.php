<?php
// Block run of MS SQL Server code for now.
if (Database::$type === 'mssql') {
	return false;
}

// Make sure FOCUS-13030 has run.
Migrations::depend('FOCUS-13030');

// Block run if immunizations isn't installed.
if (!Database::tableExists('imm_config')) {
	return false;
}

// Update end date for old rulesets.
Database::query("update imm_rulesets_rules set end_dt = 2017 where ruleset_id in (select id from imm_rulesets where title in ('Florida:Varicella:RS1', 'Florida:Varicella:RS2')) and start_dt = 1970;");

// Add new rulesets.
Database::query("insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
	(select id from imm_rulesets where title = 'Florida:Varicella:RS1'),
	(select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
	1,
	2018,
	null,
	'Y',
	null,
	null,
	null
);");
Database::query("insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
	(select id from imm_rulesets where title = 'Florida:Varicella:RS2'),
	(select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
	1,
	2018,
	null,
	'Y',
	'>=',
	'2',
	'Requires 2 doses.'
);");

// Add new dynamic values.
Database::query("insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''PK'', ''11'', ''12''');");
Database::query("insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08'', ''09'', ''10''');");

// Add new ruleset_rules_dynamic_values.
Database::query("insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
	(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1 and start_dt = 2018),
	(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
	1
);");
Database::query("insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
	(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1 and start_dt = 2018),
	(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''11'', ''12'''),
	2
);");
Database::query("insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
	(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1 and start_dt = 2018),
	(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
	1
);");
Database::query("insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
	(select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1 and start_dt = 2018),
	(select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08'', ''09'', ''10'''),
	2
);");
