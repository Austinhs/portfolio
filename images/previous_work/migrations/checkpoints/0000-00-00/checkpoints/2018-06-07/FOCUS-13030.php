<?php
// Block run of MS SQL Server code for now.
if (Database::$type === 'mssql') {
	return false;
}

// Block if not Florida.
if (strtolower($GLOBALS['_FOCUS']['config']['state_name']) !== 'florida') {
	return false;
}

// Block run if immunizations already installed.
if (Database::tableExists('imm_vaccines')) {
	return false;
}

// Make sure FOCUS-13336 has run so the sis column is added to custom_field_categories
Migrations::depend('FOCUS-13336');

// Make sure FOCUS-10748 has run so that the description column is added to custom_reports.
Migrations::depend('FOCUS-10748');

// Make sure FOCUS-11510 has run so that the execute_only column is added to custom_reports.
Migrations::depend('FOCUS-11510');

$field = new CustomField();
$field
	->setSourceClass('SISStudent')
	->setTitle('Immunization Compliance')
	->setAlias('immunization_compliance')
	->setSystem(1)
	->setType('log')
	->persist();

$fieldId = $field->getId();

$column = new CustomFieldLogColumn();
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD1')
	->setType('select')
	->setTitle('Vaccination')
	->setSortOrder(1)
	->persist();

$column = new CustomFieldLogColumn();
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD2')
	->setType('text')
	->setTitle('Compliance')
	->setSortOrder(2)
	->persist();

$column = new CustomFieldLogColumn();
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD3')
	->setType('text')
	->setTitle('Rule Description')
	->setSortOrder(3)
	->persist();

$column = new CustomFieldLogColumn();
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD4')
	->setType('text')
	->setTitle('Exemption Status')
	->setSortOrder(4)
	->persist();

$field = new CustomField();
$field
	->setSourceClass('SISStudent')
	->setTitle('Immunization Exemption')
	->setAlias('immunization_exemption')
	->setSystem(1)
	->setType('log')
	->persist();

$fieldId = $field->getId();

$column = new CustomFieldLogColumn();
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD1')
	->setType('select')
	->setTitle('Immunization')
	->setSortOrder(1)
	->persist();

$column = new CustomFieldLogColumn();
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD2')
	->setType('select')
	->setTitle('Exemption')
	->setSortOrder(2)
	->persist();

$colId = $column->getId();

$option = new CustomFieldSelectOption();
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('ROC')
	->setLabel('Reasons of Conscience')
	->persist();

$option = new CustomFieldSelectOption();
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('ME')
	->setLabel('Medical Exemption')
	->persist();

$option = new CustomFieldSelectOption();
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('MELL')
	->setLabel('Medical Exemption-Life Long')
	->persist();

$option = new CustomFieldSelectOption();
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('SC')
	->setLabel('Serological Confirmation')
	->persist();

$column = new CustomFieldLogColumn();
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD3')
	->setType('date')
	->setTitle('Exemption Date')
	->setSortOrder(3)
	->persist();

$column = new CustomFieldLogColumn();
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD4')
	->setType('date')
	->setTitle('Exemption Expiration Date')
	->setSortOrder(4)
	->persist();


if (Database::$type === 'mssql') {
	// 5. Projects/Immunizations/Dynamic Version/SQL Server
  $sql = [
		// 1. Design Model/imm_objects.sql
    "CREATE TABLE imm_config (
			title                varchar(50) NOT NULL   ,
			code                 varchar(250)    ,
			CONSTRAINT pk_config_title PRIMARY KEY  ( title )
		 );",
		"CREATE TABLE imm_dynamic_values (
			id                   int NOT NULL   IDENTITY,
			dv_type              varchar(3) NOT NULL   ,
			dv_value             varchar(250) NOT NULL   ,
			CONSTRAINT pk_dynamic_values PRIMARY KEY  ( id ),
			CONSTRAINT dynamic_values_uk_i UNIQUE ( dv_type, dv_value )
		 );",
		"CREATE TABLE imm_immunizations (
			id                   int NOT NULL   IDENTITY,
			title                varchar(50)    ,
			code                 varchar(5)    ,
			CONSTRAINT pk_immunizations PRIMARY KEY  ( id )
		 );",
		"CREATE TABLE imm_rules (
			id                   int NOT NULL   IDENTITY,
			code                 varchar(1000) NOT NULL   ,
			CONSTRAINT pk_rules PRIMARY KEY  ( id ),
			CONSTRAINT rules_uk_i UNIQUE ( code )
		 );",
		"CREATE TABLE imm_ruleset_groups (
			id                   int NOT NULL   IDENTITY,
			title                varchar(50)    ,
			description          varchar(250)    ,
			CONSTRAINT pk_ruleset_groups_0 PRIMARY KEY  ( id )
		 );",
		"CREATE TABLE imm_rulesets (
			id                   int NOT NULL   IDENTITY,
			title                varchar(50)    ,
			compliance_msg       varchar(250)    ,
			error_msg            varchar(250)    ,
			CONSTRAINT pk_rulesets PRIMARY KEY  ( id )
		 );",
		"CREATE TABLE imm_rulesets_rules (
			id                   int NOT NULL   IDENTITY,
			ruleset_id           int    ,
			rule_id              int    ,
			rule_order           int    ,
			start_dt             int    ,
			end_dt               int    ,
			grade_limiter        varchar(1)    ,
			operand              varchar(5)    ,
			score                varchar(5)    ,
			error_msg            varchar(250)    ,
			CONSTRAINT pk_rulesets_rules PRIMARY KEY  ( id )
		 );",
		"CREATE  INDEX idx_rulesets_rules ON imm_rulesets_rules ( ruleset_id );",
		"CREATE  INDEX idx_rulesets_rules_0 ON imm_rulesets_rules ( rule_id );",
		"CREATE TABLE imm_rulesets_rules_dynamic_values (
			rulesets_rules_id    int    ,
			dynamic_value_id     int    ,
			value_order          int
		 );",
		"CREATE  INDEX idx_rulesets_rules_dynamic_values ON imm_rulesets_rules_dynamic_values ( rulesets_rules_id );",
		"CREATE  INDEX idx_rulesets_rules_dynamic_values_0 ON imm_rulesets_rules_dynamic_values ( dynamic_value_id );",
		"CREATE TABLE imm_vaccines (
			immunization_id      int    ,
			ruleset_group_id     int    ,
			title                varchar(50) NOT NULL   ,
			code                 varchar(25) NOT NULL
		 );",
		"CREATE  INDEX idx_imm_vaccines_immunization_id ON imm_vaccines ( immunization_id );",
		"CREATE  INDEX idx_imm_vaccines ON imm_vaccines ( ruleset_group_id );",
		"CREATE TABLE imm_ruleset_groups_rulesets (
			ruleset_group_id     int    ,
			ruleset_id           int    ,
			immunization_id      int    ,
			imm_order            int    ,
			start_dt             int    ,
			end_dt               int    ,
			compliance_msg       varchar(250)    ,
			error_msg            varchar(250)
		 );",
		"CREATE  INDEX idx_ruleset_groups_rulesets_immunization_id ON imm_ruleset_groups_rulesets ( immunization_id );",
		"CREATE  INDEX idx_ruleset_groups_rulesets_ruleset_group_id ON imm_ruleset_groups_rulesets ( ruleset_group_id );",
		"CREATE  INDEX idx_ruleset_groups_rulesets_ruleset_id ON imm_ruleset_groups_rulesets ( ruleset_id );",
		"ALTER TABLE imm_ruleset_groups_rulesets ADD CONSTRAINT fk_ruleset_groups_rulesets_immunization_id FOREIGN KEY ( immunization_id ) REFERENCES imm_immunizations( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",
		"ALTER TABLE imm_ruleset_groups_rulesets ADD CONSTRAINT fk_ruleset_groups_rulesets_ruleset_group_id FOREIGN KEY ( ruleset_group_id ) REFERENCES imm_ruleset_groups( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",
		"ALTER TABLE imm_ruleset_groups_rulesets ADD CONSTRAINT fk_ruleset_groups_rulesets_ruleset_id FOREIGN KEY ( ruleset_id ) REFERENCES imm_rulesets( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",
		"ALTER TABLE imm_rulesets_rules ADD CONSTRAINT fk_rulesets_rules_rulesets FOREIGN KEY ( ruleset_id ) REFERENCES imm_rulesets( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",
		"ALTER TABLE imm_rulesets_rules ADD CONSTRAINT fk_rulesets_rules_rules FOREIGN KEY ( rule_id ) REFERENCES imm_rules( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",
		"ALTER TABLE imm_rulesets_rules_dynamic_values ADD CONSTRAINT fk_rulesets_rules_dynamic_values FOREIGN KEY ( rulesets_rules_id ) REFERENCES imm_rulesets_rules( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",
		"ALTER TABLE imm_rulesets_rules_dynamic_values ADD CONSTRAINT fk_rulesets_rules_dynamic_values1 FOREIGN KEY ( dynamic_value_id ) REFERENCES imm_dynamic_values( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",
		"ALTER TABLE imm_vaccines ADD CONSTRAINT fk_imm_vaccines_immunization_id FOREIGN KEY ( immunization_id ) REFERENCES imm_immunizations( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",
		"ALTER TABLE imm_vaccines ADD CONSTRAINT fk_imm_vaccines_ruleset_groups FOREIGN KEY ( ruleset_group_id ) REFERENCES imm_ruleset_groups( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;",

		// 2. ../Generic Loadout/program_config.sql
		"insert into program_config (syear, program, title, value) values (
		  (select value from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null),
		  'system',
		  'IMM_COMPLIANCE',
		  'N'
		);",
		"insert into program_config (syear, program, title, value) values (
		  (select value from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null),
		  'system',
		  'IMM_SELECTED_RULESET_GROUP',
		  'Florida'
		);",
		"insert into program_config (syear, program, title, value) values (
		  (select value from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null),
		  'system',
		  'IMM_ERROR_HANDLING',
		  'Ruleset'
		);",
		"insert into program_config (syear, program, title, value) values (
		  (select value from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null),
		  'system',
		  'IMM_INTERVAL_DAYS',
		  '4'
		);",

		// 3. Default Loadout/imm_config.sql
		"insert into imm_config (title, code) values (
		  'IMM_CUSTOM_COL_NAME',
		  (select column_name from custom_fields where alias = 'immunization_compliance' and type = 'log' and source_class = 'SISStudent' and deleted is null)
		);",
		"insert into imm_config (title, code) values (
		  'IMM_DATA_COLUMNS',
		  (select stuff((select concat(', ', lower(column_name)) from custom_field_log_columns where field_id = (select id from custom_fields where legacy_id in (400000929, 929) and type = 'log' and source_class = 'SISStudent' and deleted is null) and type = 'date' and title like '%[0-9]%' and deleted is null for xml path('')), 1, 1, ''))
		);",
		"insert into imm_config (title, code) values (
		  'IMM_SHOTS_FIELD_ID',
		  (select id from custom_fields where legacy_id in (400000929, 929) and type = 'log' and source_class = 'SISStudent' and deleted is null)
		);",
		"insert into imm_config (title, code) values (
		  'IMM_VAC_SOURCE_ID',
		  (select id from custom_field_log_columns where field_id = (select id from custom_fields where alias = 'immunization_compliance' and type = 'log' and source_class = 'SISStudent' and deleted is null) and type = 'select')
		);",
		"insert into imm_config (title, code) values (
		  'IMM_EXEMPT_FIELD_ID',
		  (select id from custom_fields where alias = 'immunization_exemption' and type = 'log' and source_class = 'SISStudent' and deleted is null)
		);",
		"insert into imm_config (title, code) values (
		  'IMM_EXEMPT_IMM_SOURCE_ID',
		  (select id from custom_field_log_columns where field_id = (select id from custom_fields where alias = 'immunization_exemption' and type = 'log' and source_class = 'SISStudent' and deleted is null) and type = 'select' and title = 'Immunization' and deleted is null)
		);",
		"insert into imm_config (title, code) values (
		  'IMM_EXEMPT_EXEMPT_SOURCE_ID',
		  (select id from custom_field_log_columns where field_id = (select id from custom_fields where alias = 'immunization_exemption' and type = 'log' and source_class = 'SISStudent' and deleted is null) and type = 'select' and title = 'Exemption' and deleted is null)
		);",

		// 4. ../Generic Loadout/imm_immunizations.sql
		"insert into imm_immunizations (title, code) values ('DTaP', 'dtp');",
		"insert into imm_immunizations (title, code) values ('Hep A', 'hepa');",
		"insert into imm_immunizations (title, code) values ('Hep B', 'hepb');",
		"insert into imm_immunizations (title, code) values ('HIB', 'hib');",
		"insert into imm_immunizations (title, code) values ('Measles', 'ms');",
		"insert into imm_immunizations (title, code) values ('Meningococcal', 'men');",
		"insert into imm_immunizations (title, code) values ('Mumps', 'mu');",
		"insert into imm_immunizations (title, code) values ('PNC', 'pnc');",
		"insert into imm_immunizations (title, code) values ('Polio', 'pol');",
		"insert into imm_immunizations (title, code) values ('Rubella', 'rub');",
		"insert into imm_immunizations (title, code) values ('Varicella', 'var');",
		"insert into imm_immunizations (title, code) values ('Hep B2', 'hepb2');",
		"insert into imm_immunizations (title, code) values ('Hep B3', 'hepb3');",

		// 5. ../Generic Loadout/imm_ruleset_groups.sql
		"insert into imm_ruleset_groups (title, description) values ('Texas','Rules from Texas.');",
		"insert into imm_ruleset_groups (title, description) values ('Florida','Rules from Florida.');",

		// 6. ../Generic Loadout/imm_vaccines.sql
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'PNC'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pneumococcal23', 'P23');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Diphtheria-Tetanus-Pertussis', 'DTP');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Diphtheria-Tetanus-Acellular-Pertussis', 'DTaP');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Tetanus-Diphtheria', 'Td');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Diphtheria-Tetanus', 'DT');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep A'), (select id from imm_ruleset_groups where title = 'Texas'), 'Hepatitis A', 'HepA');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep B'), (select id from imm_ruleset_groups where title = 'Texas'), 'Hepatitis B', 'HepB');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Texas'), 'Haemophilus influenza type b', 'Hib');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Rubella'), (select id from imm_ruleset_groups where title = 'Texas'), 'Measles-Mumps-Rubella', 'MMR');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Mumps'), (select id from imm_ruleset_groups where title = 'Texas'), 'Measles-Mumps-Rubella', 'MMR');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Texas'), 'Measles-Mumps-Rubella', 'MMR');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'Meningococcal', 'MN');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'Meningococcal B, recombinant', 'MNB');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'PNC'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pneumococcal Conjugate', 'PC');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Inactivated Polio', 'PV');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Oral Polio', 'OPV');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Tdap', 'Tdap');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Varicella'), (select id from imm_ruleset_groups where title = 'Texas'), 'Varicella', 'VA');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Texas'), 'Measles', 'Measles');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'MCV4', 'MCV4');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'MenACWY', 'MenACWY');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'Menveo', 'Menveo');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'Menactra', 'Menactra');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Kinrix', 'Kinrix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Kinrix', 'Kinrix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pediarix', 'Pediarix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep B'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pediarix', 'Pediarix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pediarix', 'Pediarix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pentacel', 'Pentacel');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pentacel', 'Pentacel');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pentacel', 'Pentacel');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Texas'), 'Comvax', 'Comvax');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'MenHibrix', 'MenHibrix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Texas'), 'MenHibrix', 'MenHibrix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Varicella'), (select id from imm_ruleset_groups where title = 'Texas'), 'ProQuad', 'ProQuad');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Rubella'), (select id from imm_ruleset_groups where title = 'Texas'), 'ProQuad', 'ProQuad');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Mumps'), (select id from imm_ruleset_groups where title = 'Texas'), 'ProQuad', 'ProQuad');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Texas'), 'ProQuad', 'ProQuad');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'PNC'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pneumococcal13 ', 'PCV13');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'DTP vaccine', 'A');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'DT (Pediatric) vaccine', 'B');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'Tdap (Tetanus-Diphtheria-Pertussis) vaccine', 'P');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'Td (Tetanus-Diphtheria) vaccine', 'Q');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep B3'), (select id from imm_ruleset_groups where title = 'Florida'), 'Hepatitis B vaccine - 3 doses', 'J');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Florida'), 'Hib Haemophilus Influenza Type B vaccine', 'E');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Rubella'), (select id from imm_ruleset_groups where title = 'Florida'), 'MMR (Measles, Mumps and Rubella) vaccine', 'F');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Mumps'), (select id from imm_ruleset_groups where title = 'Florida'), 'MMR (Measles, Mumps and Rubella) vaccine', 'F');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Florida'), 'MMR (Measles, Mumps and Rubella) vaccine', 'F');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Florida'), 'Measles (Rubeola) vaccine', 'G');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Mumps'), (select id from imm_ruleset_groups where title = 'Florida'), 'Mumps vaccine', 'H');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Rubella'), (select id from imm_ruleset_groups where title = 'Florida'), 'Rubella vaccine', 'I');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'PNC'), (select id from imm_ruleset_groups where title = 'Florida'), 'Pneumococcal Conjugate vaccine', 'N');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Florida'), 'Polio vaccine', 'D');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Varicella'), (select id from imm_ruleset_groups where title = 'Florida'), 'VZV (Varicella) vaccine', 'K');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Varicella'), (select id from imm_ruleset_groups where title = 'Florida'), 'Varicella disease', 'L');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'Td Adult vaccine or Tdap vaccine', 'C');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep B2'), (select id from imm_ruleset_groups where title = 'Florida'), 'Hepatitis B vaccine - 2 doses', 'M');",

		// 7. ../Generic Loadout/imm_rulesets.sql
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS1', '5 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS2', '4 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS3', '3 doses, 1 on or after 4th birthday, and student is at least 7 years old.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS4', '3 doses, 1 booster within last 5 years, and student is in 7th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS5', '3 doses, 1 booster within last 10 years, and student is in 8th - 12th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Polio:RS1', '4 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Polio:RS2', '3 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Measles:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Measles:RS2', '2 doses and full MMR series happened before 2009.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Mumps:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Mumps:RS2', '1 dose and full MMR series happened before 2009.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Rubella:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Rubella:RS2', '1 dose and full MMR series happened before 2009.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Hep B:RS1', '3 doses.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Hep B:RS2', '2 doses of vaccine Recombivax recieved between 11 and 15 years of age.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Varicella:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Meningococcal:RS1', 'Not required for PK - 6th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Meningococcal:RS2', '1 dose on or after 10th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Hep A:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Hep A:RS2', 'Not required for 9th - 12th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:HIB:RS1', 'Not required after PK or after a student turns 5.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:HIB:RS2', '1 dose, on or after 15 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:HIB:RS3', '3 or 4 doses, 1 dose on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:PNC:RS1', 'Not required after PK or after a student turns 5.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:PNC:RS2', '1 dose, on or after 24 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:PNC:RS3', '2 doses, on or after 12 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:PNC:RS4', '2 or 3 doses on or before 12 months, 1 dose on or after 12 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS1', '5 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS2', '5 doses, 1 on or before 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS3', '4 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS4', '4 doses of Pediatric Diphtheria-Tetanus (DT) as substitute for DTP/DTaP due to Pertussis exemption.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS5', '3 doses of Adult Tetanus-Diphtheria (Td) with a Tdap dose replacing 1 dose.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS6', '3 doses, 1 booster within last 5 years, and student is in 7th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS7', '3 doses, 1 booster within last 10 years, and student is in 8th - 12th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Polio:RS1', '4 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Polio:RS2', '5 doses, when 4th dose before 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Polio:RS3', '3 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Polio:RS4', '3 doses, all after 7th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Measles:RS1', '2 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Mumps:RS1', '2 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Rubella:RS1', '2 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:HIB:RS1', 'Not required after PK or after a student turns 5.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:HIB:RS2', '1 dose, on or after 15 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:HIB:RS3', '3 or 4 doses, 1 dose on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Hep B2:RS1', '2 doses between 11 and 15 years of age.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Hep B2:RS2', 'Not required in before KG.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Hep B3:RS1', '3 doses.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Varicella:RS1', '1 dose.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Varicella:RS2', '2 doses.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Varicella:RS3', 'Serological confirmation.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:PNC:RS1', 'Not required after PK or after a student turns 5.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:PNC:RS2', '1 dose, on or after 24 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:PNC:RS3', '2 doses, on or after 12 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:PNC:RS4', '2 or 3 doses on or before 12 months, 1 dose on or after 12 months.', null);",

		// 8. ../Generic Loadout/imm_ruleset_groups_rulesets.sql
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS1'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS2'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS4'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS5'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Polio:RS1'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Polio:RS2'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Measles:RS1'),
		  (select id from imm_immunizations where title = 'Measles'),
		  3,
		  1970,
			null,
			'Measles compliance complete.',
		  'Measles compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Measles:RS2'),
		  (select id from imm_immunizations where title = 'Measles'),
		  3,
		  1970,
			null,
			'Measles compliance complete.',
		  'Measles compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS1'),
		  (select id from imm_immunizations where title = 'Mumps'),
		  4,
		  1970,
			null,
			'Mumps compliance complete.',
		  'Mumps compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS2'),
		  (select id from imm_immunizations where title = 'Mumps'),
		  4,
		  1970,
			null,
			'Mumps compliance complete.',
		  'Mumps compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS1'),
		  (select id from imm_immunizations where title = 'Rubella'),
		  5,
		  1970,
			null,
			'Rubella compliance complete.',
		  'Rubella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS2'),
		  (select id from imm_immunizations where title = 'Rubella'),
		  5,
		  1970,
			null,
			'Rubella compliance complete.',
		  'Rubella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Hep B:RS1'),
		  (select id from imm_immunizations where title = 'Hep B'),
		  6,
		  1970,
			null,
			'Hep B compliance complete.',
		  'Hep B compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Hep B:RS2'),
		  (select id from imm_immunizations where title = 'Hep B'),
		  6,
		  1970,
			null,
			'Hep B compliance complete.',
		  'Hep B compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Varicella:RS1'),
		  (select id from imm_immunizations where title = 'Varicella'),
		  7,
		  1970,
			null,
			'Varicella compliance complete.',
		  'Varicella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS1'),
		  (select id from imm_immunizations where title = 'Meningococcal'),
		  8,
		  1970,
			null,
			'Meningococcal compliance complete.',
		  'Meningococcal compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2'),
		  (select id from imm_immunizations where title = 'Meningococcal'),
		  8,
		  1970,
			null,
			'Meningococcal compliance complete.',
		  'Meningococcal compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS1'),
		  (select id from imm_immunizations where title = 'Hep A'),
		  9,
		  1970,
			null,
			'Hep A compliance complete.',
		  'Hep A compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS2'),
		  (select id from imm_immunizations where title = 'Hep A'),
		  9,
		  1970,
			null,
			'Hep A compliance complete.',
		  'Hep A compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:HIB:RS1'),
		  (select id from imm_immunizations where title = 'HIB'),
		  10,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:HIB:RS2'),
		  (select id from imm_immunizations where title = 'HIB'),
		  10,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:HIB:RS3'),
		  (select id from imm_immunizations where title = 'HIB'),
		  10,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:PNC:RS1'),
		  (select id from imm_immunizations where title = 'PNC'),
		  11,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:PNC:RS2'),
		  (select id from imm_immunizations where title = 'PNC'),
		  11,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:PNC:RS3'),
		  (select id from imm_immunizations where title = 'PNC'),
		  11,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:PNC:RS4'),
		  (select id from imm_immunizations where title = 'PNC'),
		  11,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS1'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS2'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS3'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS4'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS5'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS6'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS7'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Polio:RS1'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Polio:RS2'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Polio:RS3'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Polio:RS4'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Measles:RS1'),
		  (select id from imm_immunizations where title = 'Measles'),
		  3,
		  1970,
			null,
			'Measles compliance complete.',
		  'Measles compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Mumps:RS1'),
		  (select id from imm_immunizations where title = 'Mumps'),
		  4,
		  1970,
			null,
			'Mumps compliance complete.',
		  'Mumps compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Rubella:RS1'),
		  (select id from imm_immunizations where title = 'Rubella'),
		  5,
		  1970,
			null,
			'Rubella compliance complete.',
		  'Rubella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:HIB:RS1'),
		  (select id from imm_immunizations where title = 'HIB'),
		  6,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:HIB:RS2'),
		  (select id from imm_immunizations where title = 'HIB'),
		  6,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:HIB:RS3'),
		  (select id from imm_immunizations where title = 'HIB'),
		  6,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Hep B2:RS1'),
		  (select id from imm_immunizations where title = 'Hep B2'),
		  7,
		  1970,
			null,
			'Hep B2 compliance complete.',
		  'Hep B2 compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Hep B2:RS2'),
		  (select id from imm_immunizations where title = 'Hep B2'),
		  7,
		  1970,
			null,
			'Hep B2 compliance complete.',
		  'Hep B2 compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Hep B3:RS1'),
		  (select id from imm_immunizations where title = 'Hep B3'),
		  8,
		  1970,
			null,
			'Hep B3 compliance complete.',
		  'Hep B3 compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS1'),
		  (select id from imm_immunizations where title = 'Varicella'),
		  9,
		  1970,
			null,
			'Varicella compliance complete.',
		  'Varicella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS2'),
		  (select id from imm_immunizations where title = 'Varicella'),
		  9,
		  1970,
			null,
			'Varicella compliance complete.',
		  'Varicella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS3'),
		  (select id from imm_immunizations where title = 'Varicella'),
		  9,
		  1970,
			null,
			'Varicella compliance complete.',
		  'Varicella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:PNC:RS1'),
		  (select id from imm_immunizations where title = 'PNC'),
		  10,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:PNC:RS2'),
		  (select id from imm_immunizations where title = 'PNC'),
		  10,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:PNC:RS3'),
		  (select id from imm_immunizations where title = 'PNC'),
		  10,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:PNC:RS4'),
		  (select id from imm_immunizations where title = 'PNC'),
		  10,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",

		// 9. Default Loadout/imm_rules.sql
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast(dateadd(:dv3:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv4:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast(dateadd(:dv2:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv3:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)');",

		// 10. ../Generic Loadout/imm_dynamic_values.sql
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'DTaP');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '4 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('int', '5');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('int', '4');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('int', '3');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '7 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''07''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''08'', ''09'', ''10'', ''11'', ''12''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''Tdap'', ''Td''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '5 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '10 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Polio');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Measles');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('int', '2');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '1 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '12/31/2008');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Mumps');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Rubella');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Hep B');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Recombivax');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '11 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '15 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Varicella');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Meningococcal');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''07'', ''08'', ''09'', ''10'', ''11'', ''12''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Hep A');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''09'', ''10'', ''11'', ''12''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'HIB');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''EE'', ''PK''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '15 months');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'PNC');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '2 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'B');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''Q'', ''P''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''KG''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Hep B2');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Hep B3');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''PK'', ''10'', ''11'', ''12''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08'', ''09''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'L');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Q');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'P');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 4');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 10');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 1');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'month, 15');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 2');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 7');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 5');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 11');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 15');",

		// 11. Default Loadout/imm_rulesets_rules.sql
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 5 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  4,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Student must be at least 7 years old.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 booster within last 5 years.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 booster within last 10 years.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Polio:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4 doses with 1 being on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Polio:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses with 1 being on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Measles:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses with 1 being on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Measles:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses of Measles after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Measles:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Mumps after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Measles:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Rubella after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses with 1 being on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses of Measles after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Mumps after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Rubella after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses with 1 being on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses of Measles after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Mumps after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Rubella after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep B:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep B:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast(dateadd(:dv3:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv4:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses of vaccine Recombivax recieved between 11 and 15 years of age.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Varicella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses, 1 dose on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 10th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:HIB:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Not required after PK or after a student turns 5.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:HIB:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 15 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:HIB:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires at least 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:HIB:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Not required after PK or after a student turns 5.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requres 1 dose on or after 24 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses on or after 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires at least 2 doses on or before 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires a shot on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 5 doses with the 5th dose on or before 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4 doses on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '4',
		  'Requires 4 doses of DT.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '=',
		  '0',
		  'Student must start DTaP immunization after age 7 to comply with this rule.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses of Td.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  '>=',
		  '1',
		  'Requires 1 dose of Tdap to replace Td.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS6'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS6'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS6'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 booster within last 5 years.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS7'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS7'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS7'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 booster within last 10 years.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires dose after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4th dose before 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 5 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3rd dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '3',
		  'Requires 3rd dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Measles:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Measles:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Mumps:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Mumps:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Rubella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Rubella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:HIB:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Not required after PK or after a student turns 5.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:HIB:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 15 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:HIB:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires at least 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:HIB:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Hep B2:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast(dateadd(:dv2:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv3:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses between 11th and 15th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Hep B2:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Hep B3:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  '>=',
		  '2',
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires serological confirmation.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Not required after PK or after a student turns 5.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requres 1 dose on or after 24 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses on or after 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires at least 2 doses on or before 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 12 months.'
		);",

		// 12. Default Loadout/imm_rulesets_rules_dynamic_values.sql
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '5'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 4),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 4),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 7'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''07'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''Tdap'', ''Td'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 5'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''08'', ''09'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''Tdap'', ''Td'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 10'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) and cast(dateadd(day, days_interval, shot_date) as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast(dateadd(:dv3:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv4:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast(dateadd(:dv3:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv4:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Recombivax'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast(dateadd(:dv3:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv4:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 11'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast(dateadd(:dv3:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv4:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 15'),
		  4
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Meningococcal'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Meningococcal'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''07'', ''08'', ''09'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Meningococcal'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 10'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep A'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep A'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep A'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep A'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''09'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 5'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'month, 15'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 5'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '5'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'B'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 7'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Q'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'P'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''07'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''Q'', ''P'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 5'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''08'', ''09'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''Q'', ''P'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and dateadd(day, days_interval, getdate()) <= cast(dateadd(:dv3:, shot_date) as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 10'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''KG'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '5'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv3:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 7'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 5'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'month, 15'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast(dateadd(:dv2:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv3:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B2'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast(dateadd(:dv2:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv3:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 11'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast(dateadd(:dv2:, dateadd(day, -days_interval, dob)) as date) and cast(dateadd(:dv3:, dateadd(day, days_interval, dob)) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 15'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B2'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B3:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B3'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B3:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num = :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08'', ''09'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'L'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or dateadd(day, days_interval, getdate()) >= cast(dateadd(:dv3:, dob) as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 5'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) <= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(dateadd(day, days_interval, shot_date) as date) >= cast(dateadd(:dv2:, dob) as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'year, 1'),
		  2
		);",

		// 13. Types/IMMCalcDataType.sql
		"create type IMMCalcDataType as table (
			student_id bigint,
			gradelevel varchar(5),
			dob date,
			days_interval int,
			age int,
			age_in_months int,
			state_code varchar(25),
			vaccine_class varchar(25),
			vaccine_id int,
			shot_date date,
			vac_dose_num int,
			class_dose_num int,
			vac_lag_time_days int,
			class_lag_time_days int,
			stu_imm_id int
		);",

		// 14. Types/IMMGetRulesetsType.sql
		"create type IMMGetRulesetsType as table (
		  title varchar(100),
		  t1error varchar(1000),
		  t2error varchar(1000),
		  t3error varchar(1000),
		  t1comp varchar(1000),
		  t2comp varchar(1000),
		  code varchar(4000),
		  grade_limiter varchar(5),
		  operand varchar(5),
		  score varchar(5),
		  rule_order int
		);",

		// 15. Procedures/sp_imm_calc_data.sql
		"create procedure sp_imm_calc_data(
			@p_student_id int,
			@p_syear int
		)
		as
		begin
			set ansi_warnings off;
			set nocount on;

			declare
				@v_sql varchar(max),
				@v_shot_dates varchar(250),
				@v_selected_ruleset_group_id int,
				@v_syear int,
				@v_student_id varchar(50);

			if @p_syear is null
				set @v_syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null)
			else
				set @v_syear = @p_syear;

			set @v_shot_dates = (select code from imm_config where title = 'IMM_DATA_COLUMNS');

			set @v_selected_ruleset_group_id = (select id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = @v_syear and school_id is null));

		  if @p_student_id is null
		    set @v_student_id = 'null'
		  else
		    set @v_student_id = cast(@p_student_id as varchar);

			set @v_sql = '
				with vars as (
					select
						(select ' + cast(@v_syear as varchar) + ') syear,
						(select ' + cast(@v_selected_ruleset_group_id as varchar) + ') vruleset_group_id
				),
				student_info as (
					select
						se.student_id,
						se.gradelevel,
						cast(s.custom_200000004 as date) dob,
						datediff(hour, cast(s.custom_200000004 as date), getdate()) / 8766 age
					from (
						select
							se.end_date,
							se.student_id,
							sg.short_name gradelevel,
							row_number() over (partition by se.student_id order by se.end_date desc) rn
						from student_enrollment se
						join school_gradelevels sg
							on sg.id = se.grade_id
						where se.syear = (select syear from vars)
							and (se.student_id = ' + @v_student_id + ' or coalesce(' + @v_student_id + ', 1) = 1)
						group by se.end_date, se.student_id, sg.short_name) se
					join students s
						on s.student_id = se.student_id
					where se.rn = 1
				),
				cjoin_data as (
					select
						si.student_id,
						si.gradelevel,
						si.dob,
						si.age,
						cfso.id,
						cfso.code,
						cfso.label
					from student_info si
					cross join custom_field_select_options cfso
					where cfso.source_id = (select cast(code as int) from imm_config where title = ''IMM_VAC_SOURCE_ID'')
				),
				shot_info as (
					select
						x.cfle_id,
						x.cfso_id,
						x.student_id,
						x.title,
						x.code,
						x.vaccine_id,
						rank() over (partition by x.student_id, x.title, x.vaccine_id order by cast(x.shot_dates as date)) vac_dose_num,
						rank() over (partition by x.student_id, x.title order by cast(x.shot_dates as date)) class_dose_num,
						cast(x.shot_dates as date) shot_date
					from (
						select
							cfle.id cfle_id,
							cfso.id cfso_id,
							cfle.source_id student_id,
							cfso.code,
							i.title,
							cfso.id vaccine_id,
							cfle.shot_dates
						from (
							select
								sdup.id,
								sdup.source_id,
								sdup.log_field1,
								sdup.shot_dates
							from (
								select
									cfle.id,
									cfle.source_id,
									cfle.log_field1,
									' + @v_shot_dates + '
								from custom_field_log_entries cfle
								where cfle.source_class = ''SISStudent''
									and cfle.field_id = (select cast(code as int) from imm_config where title = ''IMM_SHOTS_FIELD_ID'')
									and (cfle.source_id = ' + @v_student_id + ' or coalesce(' + @v_student_id + ', 1) = 1)
							) sd
							unpivot
								(shot_dates for shot_column in
									(' + @v_shot_dates + ')
								) as sdup
							where isdate(sdup.shot_dates) = 1
						) cfle
						join custom_field_select_options cfso
							on cast(cfso.id as varchar) = cfle.log_field1
						join imm_vaccines iv
							on lower(iv.code) = lower(cfso.code)
						join imm_immunizations i
							on i.id = iv.immunization_id
						where iv.ruleset_group_id = (select vruleset_group_id from vars)
					) x
					join students s
						on s.student_id = x.student_id
					where isdate(x.shot_dates) = 1
						and cast(x.shot_dates as date) >= cast(s.custom_200000004 as date)
				)
				select
					cast(cd.student_id as bigint) student_id,
					cd.gradelevel,
					cd.dob,
					(select cast(value as int) from program_config where program = ''system'' and title = ''IMM_INTERVAL_DAYS'' and syear = (select syear from vars) and school_id is null) days_interval,
					cast(cd.age as integer) age,
					cast(floor(cast(abs(datediff(day, cd.dob, si.shot_date)) as float) / cast(30.27 as float)) as integer) age_in_months,
					si.code state_code,
					i.title vaccine_class,
					cast(si.cfso_id as integer) vaccine_id,
					si.shot_date,
					cast(si.vac_dose_num as integer) vac_dose_num,
					cast(si.class_dose_num as integer) class_dose_num,
					case when si.vac_dose_num = 1 then cast(abs(datediff(day, cd.dob, si.shot_date)) as integer)
						else cast(abs(datediff(day, (lag(si.shot_date, 1) over (partition by si.student_id, i.title, si.vaccine_id order by si.vac_dose_num)), si.shot_date)) as integer)
					end vac_lag_time_days,
					case when si.class_dose_num = 1 then cast(abs(datediff(day, cd.dob, si.shot_date)) as integer)
						else cast(abs(datediff(day, (lag(si.shot_date, 1) over (partition by si.student_id, i.title order by si.class_dose_num)), si.shot_date)) as integer)
					end class_lag_time_days,
					cast(si.cfle_id as integer) stu_imm_id
				from cjoin_data cd
				join imm_immunizations i
					on lower(i.code) = lower(cd.code)
				left join shot_info si
					on si.student_id = cd.student_id and si.title = i.title
				where si.shot_date >= cd.dob or si.shot_date is null';

			execute (@v_sql);

			return;
		end;",

		// 16. Procedures/sp_imm_get_rulesets.sql
		"create procedure sp_imm_get_rulesets (
		  @p_ruleset_group varchar(25),
		  @p_immunization varchar(10),
		  @p_syear int
		)
		as
		begin
			set ansi_warnings off;
			set nocount on;

		  declare
		    @v_sql varchar(max);

			set @v_sql = '
		  	with vars as (
		  		select
		  			(select id from imm_ruleset_groups rg where rg.title = ''' + @p_ruleset_group + ''') ruleset_group_id,
		  			(select id from imm_immunizations i where i.code = ''' + @p_immunization + ''') immunization_id,
		        (select ' + cast(@p_syear as varchar) + ') syear
		  	),
		  	dv_vars as (
		  	select
		  		rr.id,
		  		rrdv.value_order,
		  		dv.dv_value
		  	from imm_rulesets_rules rr
		  	join imm_rulesets rs
		  	  on rs.id = rr.ruleset_id
		  	join imm_ruleset_groups_rulesets rgr
		  	  on rgr.ruleset_id = rs.id
		  	join imm_rulesets_rules_dynamic_values rrdv
		  	  on rrdv.rulesets_rules_id = rr.id
		  	join imm_dynamic_values dv
		  	  on dv.id = rrdv.dynamic_value_id
		  	where rgr.ruleset_group_id = (select ruleset_group_id from vars)
		  		and rgr.immunization_id = (select immunization_id from vars)
		      and ((select syear from vars) between rgr.start_dt and rgr.end_dt or (rgr.end_dt is null and rgr.start_dt <= (select syear from vars)))
		      and ((select syear from vars) between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= (select syear from vars)))
		  	),
		  	dynamic_rules as (
		  	select
		  		rr.id,
		  		case (select max(value_order) from dv_vars where id = rr.id)
		  		when 5 then
		  			replace(replace(replace(replace(
		  			replace(r.code, (select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 2), (select dv_value from dv_vars where id = rr.id and value_order = 2)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 3), (select dv_value from dv_vars where id = rr.id and value_order = 3)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 4), (select dv_value from dv_vars where id = rr.id and value_order = 4)),
		        (select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 5), (select dv_value from dv_vars where id = rr.id and value_order = 5))
		  		when 4 then
		  			replace(replace(replace(
		  			replace(r.code, (select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 2), (select dv_value from dv_vars where id = rr.id and value_order = 2)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 3), (select dv_value from dv_vars where id = rr.id and value_order = 3)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 4), (select dv_value from dv_vars where id = rr.id and value_order = 4))
		  		when 3 then
		  			replace(replace(
		  			replace(r.code, (select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 2), (select dv_value from dv_vars where id = rr.id and value_order = 2)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 3), (select dv_value from dv_vars where id = rr.id and value_order = 3))
		  		when 2 then
		  			replace(
		  			replace(r.code, (select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1)),
		  			(select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 2), (select dv_value from dv_vars where id = rr.id and value_order = 2))
		  		when 1 then
		  			replace(r.code, (select concat('':dv'', value_order, '':'') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1))
		  		end code
		  	from imm_rules r
		  	join imm_rulesets_rules rr
		  		on rr.rule_id = r.id
		    where ((select syear from vars) between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= (select syear from vars)))
		  	)
		  	select
		  		rs.title title,
		  		rgr.error_msg t1error,
		  		coalesce(rs.error_msg, rs.compliance_msg) t2error,
		  		rr.error_msg t3error,
		  		rgr.compliance_msg t1comp,
		  		rs.compliance_msg t2comp,
		  		dr.code code,
		  		rr.grade_limiter,
		  		rr.operand,
		  		rr.score,
		      rr.rule_order
		  	from imm_ruleset_groups_rulesets rgr
		  	join imm_rulesets rs
		  		on rs.id = rgr.ruleset_id
		  	join imm_rulesets_rules rr
		  		on rr.ruleset_id = rs.id
		  	join dynamic_rules dr
		  		on dr.id = rr.id
		  	where rgr.ruleset_group_id = (select ruleset_group_id from vars)
		  		and rgr.immunization_id = (select immunization_id from vars)
		      and ((select syear from vars) between rgr.start_dt and rgr.end_dt or (rgr.end_dt is null and rgr.start_dt <= (select syear from vars)))
		      and ((select syear from vars) between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= (select syear from vars)))
		  	order by rgr.imm_order, rs.title, rr.rule_order;';

			execute (@v_sql);

			return;

		end;",

		// 17. Procedures/sp_imm_calc.sql
		"-- Requires IMMGetRulestsType and IMMCalcDataType to exist.
		create procedure sp_imm_calc(
		  @p_immunization varchar(50),
		  @p_student_id bigint,
		  @p_syear int,
		  @p_debug int
		)
		as
		begin
		  set ansi_warnings off;
		  set nocount on;

		  declare
		    @v_imm_id int,
		    @v_imm_col_id int,
		    @v_selected_ruleset_group_id int,
		    @v_offset_days int,
		    @v_error_handling varchar(25),
		    @v_added_filter varchar(50),
		    @v_sql varchar(max),
		    @v_sub_sql varchar(max),
		    @v_rs_counter int,
		    @v_rs_selector int,
		    @v_syear int,
		    @v_ruleset_group_title varchar(50),
		    @v_default_error varchar(200),
		    @ruleset_rules IMMGetRulesetsType,
		    @shot_data IMMCalcDataType;

		  -- Singleton student override query value.
		  if @p_student_id is not null
		    set @v_added_filter = concat(' and cfle.source_id = ', @p_student_id)
		  else
		    return;

		  -- Drop temp tables.
		  if object_id('tempdb..#shot_data') is not null
		    drop table #shot_data;

		  if object_id('tempdb..#rr_data') is not null
		    drop table #rr_data;

		  if object_id('tempdb..#t3e') is not null
		    drop table #t3e;

		  if object_id('tempdb..#err_item') is not null
		    drop table #err_item;

		  if object_id('tempdb..#comp_data') is not null
		    drop table #comp_data;

		  create table #t3e (
		    student_id bigint,
		    rs varchar(10) collate Latin1_General_CI_AS,
		    score int,
		    error_msg varchar(250) collate Latin1_General_CI_AS,
		    gl_test int,
		    rank int
		  );

		  create table #err_item (
		    student_id bigint,
		    imm_error_msg varchar(250) collate Latin1_General_CI_AS,
		    error_msg varchar(max) collate Latin1_General_CI_AS
		  );

		  create table #comp_data (
		    student_id bigint,
		    vaccine_id varchar(10) collate Latin1_General_CI_AS,
		    comp varchar(5) collate Latin1_General_CI_AS,
		    message varchar(1000) collate Latin1_General_CI_AS,
		    exempt varchar(250) collate Latin1_General_CI_AS,
		    custom_field_id int,
		    action varchar(5) collate Latin1_General_CI_AS
		  );

		  -- Select current syear to calculate against.
		  if @p_syear is null
		    set @v_syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null)
		  else
		    set @v_syear = @p_syear;

		  -- Catch to ensure the Dynamic Version for Immunizations system has all required variables set.
		  if (select
		      count(*)
		    from program_config
		    where title in (
		        'IMM_COMPLIANCE',
		        'IMM_SELECTED_RULESET_GROUP',
		        'IMM_ERROR_HANDLING',
		        'IMM_INTERVAL_DAYS'
		      )
		      and syear = @v_syear
		      and program = 'system'
		      and school_id is null
		      and value is null) > 0
		    return;

		  -- Check imm_config as well.
		  if (select
		      count(*)
		    from imm_config
		    where code is null) > 0
		    return;

		  -- Ensure Immunization Module is active.
		  if (select value from program_config where program = 'system' and title = 'IMM_COMPLIANCE' and syear = @v_syear and school_id is null) <> 'Y'
		    return;

		  -- Ensure an immunization was selected to run against.
		  if @p_immunization is null
		    return
		  else
		    -- Pull the immunization_id.
		    set @v_imm_id = (select id from imm_immunizations where code = @p_immunization);

		  -- Ensure an immunization was pulled.
		  if @v_imm_id is null
		    return;

		  -- Pull the column id for the custom_field to update.
		  set @v_imm_col_id = (select id from custom_fields where column_name = (select code from imm_config where title = 'IMM_CUSTOM_COL_NAME'));

		  -- Pull the selected_ruleset_group from config table.
		  set @v_selected_ruleset_group_id = (select id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = @v_syear and school_id is null));

		  -- Pull the offset_days.
		  set @v_offset_days = (select cast(value as int) from program_config where program = 'system' and title = 'IMM_INTERVAL_DAYS' and syear = @v_syear and school_id is null);

		  -- Pull error handling method.
		  set @v_error_handling = (select value from program_config where program = 'system' and title = 'IMM_ERROR_HANDLING' and syear = @v_syear and school_id is null);

		  -- Ensure an immunization was selected to run against.
		  if @p_immunization is null
		    return
		  else
		  begin
		    -- Pull the immunization_id.
		    set @v_imm_id = (
		      select distinct
		        i.id
		      from imm_immunizations i
		      join imm_vaccines v
		        on v.immunization_id = i.id
		      where i.code = @p_immunization
		        and v.ruleset_group_id = @v_selected_ruleset_group_id)
		    -- Test to make sure this immunization exists in the current selected ruleset group.
		    if @v_imm_id is null
		      return;
		  end;

		  -- Pull shot data from procedure and set to type.
		  insert @shot_data exec sp_imm_calc_data @p_student_id, @v_syear;

		  -- Set shot data into a local temporary table to be used by dynamic SQL.
		  select * into #shot_data from @shot_data;

		  set @v_ruleset_group_title = (select title from imm_ruleset_groups where id = @v_selected_ruleset_group_id);

		  -- Pull ruleset rules from procedure and set to type.
		  insert @ruleset_rules exec sp_imm_get_rulesets @v_ruleset_group_title, @p_immunization, @v_syear;

		  -- Set ruleset rules into a local temporary table to be used for looping.
		  select * into #rr_data from @ruleset_rules;

		  declare
		    @rr_counter int,
		    @sub_rr_counter int,
		    @cnt int = 1,
		    @ycnt int = 1,
		    @v_newid varchar(50) = replace(cast(newid() as varchar(50)), '-', '');

		  declare
		    @v_t2_tbl_nm varchar(50) = '##_imm_t2_' + @v_newid,
		    @v_t3_tbl_nm varchar(50) = '##_imm_t3_' + @v_newid;

		  -- Set dynamic t2 table.
		  set @v_sql = 'create table ' + @v_t2_tbl_nm + ' (student_id bigint';
		  set @rr_counter = (select count(*) from #rr_data where rule_order = 1);
		  while @cnt <= @rr_counter
		  begin
		    set @sub_rr_counter = (select max(rule_order) from #rr_data where substring(title, len(title), 1) = cast(@cnt as varchar));
		    while @ycnt <= @sub_rr_counter
		    begin
		      select @v_sql = @v_sql + ', rs' + cast(@cnt as varchar) + '_' + cast(@ycnt as varchar) + ' int'
		      select @ycnt += 1;
		    end
		    select @cnt += 1;
		    set @ycnt = 1;
		  end
		  select @v_sql = @v_sql + ');';

		  if @p_debug <> 1
		    -- Run the query.
		    execute (@v_sql)
		  else
		    -- Return the query.
		    print '1.' + char(10) + @v_sql + char(10) + char(10);

		  -- Set dynamic t3 table.
		  set @cnt = 1;
		  set @v_sql = 'create table ' + @v_t3_tbl_nm + ' (student_id bigint, vaccine_class varchar(25) collate Latin1_General_CI_AS';
		  set @rr_counter = (select count(*) from #rr_data where rule_order = 1);
		  while @cnt <= @rr_counter
		  begin
		    select @v_sql = @v_sql + ', rs' + cast(@cnt as varchar) +  ' int'
		    select @cnt += 1
		  end
		  select @v_sql = @v_sql + ');';

		  if @p_debug <> 1
		    -- Run the query.
		    execute (@v_sql)
		  else
		    -- Return the query.
		    print '2.' + char(10) + @v_sql + char(10) + char(10);

		  -- Declare cursor and cell variables.
		  declare
		    @cr cursor,
		    @cr_title varchar(100),
		    @cr_t1error varchar(1000),
		    @cr_t2error varchar(1000),
		    @cr_t3error varchar(1000),
		    @cr_t1comp varchar(1000),
		    @cr_t2comp varchar(1000),
		    @cr_code varchar(4000),
		    @cr_grade_limiter varchar(5),
		    @cr_operand varchar(5),
		    @cr_score varchar(5),
		    @cr_rule_order int,
		    @cr_rs varchar(10),
		    @cr_cfle_id int,
		    @cr_source_id bigint;

		  -- Generate @v_sql.
		  set @v_sql = '
		  with t1 as (
		    select
		      student_id,
		      vaccine_class,
		  ';

		  begin
		    set @cr = cursor for select * from #rr_data;
		    open @cr;
		    fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    while @@FETCH_STATUS = 0
		    begin
		      set @v_sql = @v_sql + @cr_code + ' ' + substring(@cr_title, len(@cr_title) - 2, len(@cr_title)) + '_' + cast(@cr_rule_order as varchar) + ', ' + char(10);
		      fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    end;
		  end;

		  set @v_sql = substring(@v_sql, 0, len(@v_sql) - 2) + char(10) + ' from #shot_data';
		  set @v_sql = @v_sql + ' group by student_id, vaccine_class),
		  t2 as (
		    select
		      student_id,
		  ';

		  begin
		    set @cr = cursor for select * from #rr_data;
		    open @cr;
		    fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    while @@FETCH_STATUS = 0
		    begin
		      if @cr_score is null
		        set @v_sql = @v_sql + 'case when max(' + substring(@cr_title, len(@cr_title) - 2, len(@cr_title)) + '_' + cast(@cr_rule_order as varchar) + ') >= 1 then 1 else 0 end ' + substring(@cr_title, len(@cr_title) - 2, len(@cr_title)) + '_' + cast(@cr_rule_order as varchar) + ', ' + char(10)
		      else
		        set @v_sql = @v_sql + 'case when max(' + substring(@cr_title, len(@cr_title) - 2, len(@cr_title)) + '_' + cast(@cr_rule_order as varchar) + ') ' + @cr_operand + ' ' + @cr_score + ' then 1 else 0 end ' + substring(@cr_title, len(@cr_title) - 2, len(@cr_title)) + '_' + cast(@cr_rule_order as varchar) + ', ' + char(10);
		      fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    end;
		  end;

		  set @v_sql = substring(@v_sql, 0, len(@v_sql) - 2) + ' from t1 group by student_id)';

		  set @v_sql = @v_sql + char(10) + ' insert into ' + @v_t2_tbl_nm + '(student_id';
		  set @cnt = 1;
		  set @ycnt = 1;
		  set @rr_counter = (select count(*) from #rr_data where rule_order = 1);
		  while @cnt <= @rr_counter
		  begin
		    set @sub_rr_counter = (select max(rule_order) from #rr_data where substring(title, len(title), 1) = cast(@cnt as varchar));
		    while @ycnt <= @sub_rr_counter
		    begin
		      select @v_sql = @v_sql + ', rs' + cast(@cnt as varchar) + '_' + cast(@ycnt as varchar)
		      select @ycnt += 1;
		    end
		    select @cnt += 1;
		    set @ycnt = 1;
		  end
		  set @v_sql = @v_sql + ') select * from t2';

		  if @p_debug <> 1
		    -- Run the query.
		    execute (@v_sql)
		  else
		    -- Return the query.
		    print '3.' + char(10) + @v_sql + char(10) + char(10);

		  set @v_sql = 'with t3 as (
		    select
		      student_id,
		      (select title from imm_immunizations where id = ' + cast(@v_imm_id as varchar) + ') vaccine_class,
		      cast(cast((';

		  -- Reset counter.
		  set @v_rs_counter = 1;
		  set @v_rs_selector = 1;

		  begin
		    set @cr = cursor for select * from #rr_data;
		    open @cr;
		    fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    while @@FETCH_STATUS = 0
		    begin
		      if @v_rs_selector <> cast(substring(@cr_title, len(@cr_title), 1) as int)
		      begin
		        set @v_sql = substring(@v_sql, 0, len(@v_sql) - 1) + ') as float)/cast(' + cast(@v_rs_counter as varchar) + ' as float)*100 as int) rs' + cast(@v_rs_selector as varchar) + ', cast(cast((';
		        set @v_sql = @v_sql + substring(@cr_title, len(@cr_title) - 2, len(@cr_title)) + '_' + cast(@cr_rule_order as varchar) + ' + ';
		        set @v_rs_selector = cast(substring(@cr_title, len(@cr_title), 1) as int);
		        set @v_rs_counter = @cr_rule_order;
		      end
		      else
		      begin
		        set @v_sql = @v_sql + ' ' + substring(@cr_title, len(@cr_title) - 2, len(@cr_title)) + '_' + cast(@cr_rule_order as varchar) + ' + ';
		        set @v_rs_counter = @cr_rule_order;
		      end;
		      fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    end;
		  end;

		  set @v_sql = substring(@v_sql, 0, len(@v_sql)) + ') as float)/cast(' + cast(@v_rs_counter as varchar) + ' as float)*100 as int) rs' + cast(@v_rs_selector as varchar) + ' from ' + @v_t2_tbl_nm + ')';

		  -- Build dynamic insert query for t3 dynamic table.
		  set @v_sql = @v_sql + char(10) + ' insert into ' + @v_t3_tbl_nm + '(student_id, vaccine_class'
		  set @cnt = 1;
		  set @rr_counter = (select count(*) from #rr_data where rule_order = 1);
		  while @cnt <= @rr_counter
		  begin
		    set @v_sql = @v_sql + ', rs' + cast(@cnt as varchar)
		    set @cnt += 1
		  end
		  set @v_sql = @v_sql + ') select * from t3';

		  if @p_debug <> 1
		    -- Run the query.
		    execute (@v_sql)
		  else
		    -- Return the query.
		    print '4.' + char(10) + @v_sql + char(10) + char(10);

		  set @v_sql = '
		  with t3pe as (
		    select
		      student_id,
		      up.rs,
		      up.score,
		      x.error_msg,
		      x.gl_test
		    from (
		      select * from ' + @v_t3_tbl_nm + ') t3
		      unpivot (
		        score for rs in (';
		  set @cnt = 1;
		  set @rr_counter = (select count(*) from #rr_data where rule_order = 1);
		  while @cnt <= @rr_counter
		  begin
		    if @cnt = 1
		      set @v_sql = @v_sql + 'rs' + cast(@cnt as varchar)
		    else
		      set @v_sql = @v_sql + ', rs' + cast(@cnt as varchar);
		    set @cnt += 1
		  end
		  set @v_sql = @v_sql + ')
		      ) up
		    join (
		      values ';

		  set @v_sub_sql = '';
		  begin
		    set @cr = cursor for select distinct
		      rs.title,
		      'rs' + substring(rs.title, len(rs.title), 1) rs,
		      rs.t2error,
		      gl.code
		      from #rr_data rs
		      left join #rr_data gl
		        on gl.title = rs.title and gl.grade_limiter = 'Y'
		      order by rs.title;
		    open @cr;
		    fetch next from @cr into @cr_title, @cr_rs, @cr_t2error, @cr_code;
		    while @@FETCH_STATUS = 0
		    begin
		      set @v_sub_sql = @v_sub_sql + '(''' + @cr_rs + ''', ''' + @cr_t2error + ''',';
		      if @cr_code is not null
		        set @v_sub_sql = @v_sub_sql + '(select ' + @cr_code + ' from #shot_data where vaccine_class = (select title from imm_immunizations where id = ' + cast(@v_imm_id as varchar) + '))), ' + char(10)
		      else
		        set @v_sub_sql = @v_sub_sql + '0), ' + char(10);
		      fetch next from @cr into @cr_title, @cr_rs, @cr_t2error, @cr_code;
		    end;
		  end;
		  set @v_sql = @v_sql + substring(@v_sub_sql, 0, len(@v_sub_sql) - 2);
		  set @v_sql = @v_sql + ') x (rs, error_msg, gl_test)
		    on x.rs = up.rs';

		  set @v_sql = @v_sql + ' )
		  insert into #t3e (student_id, rs, score, error_msg, gl_test, rank)
		  select *, rank() over (partition by student_id order by gl_test desc, score desc, rs) from t3pe';

		  if @p_debug <> 1
		    -- Run the query.
		    execute (@v_sql)
		  else
		    -- Return the query.
		    print '5.' + char(10) + @v_sql + char(10) + char(10);

		  if @v_error_handling = 'Itemized'
		  begin
		    set @v_sql = 'with eitems as (
		    	select
		    		concat(substring(r.title, len(r.title) - 2, 3), ''_'', rr.rule_order) rs,
		    		rr.error_msg,
		        rgr.error_msg imm_error_msg
		    	from imm_rulesets r
		    	join imm_rulesets_rules rr
		    		on rr.ruleset_id = r.id
		      join imm_ruleset_groups_rulesets rgr
		        on rgr.ruleset_id = r.id
		      where rgr.immunization_id = ' + cast(@v_imm_id as varchar) + '
		        and (' + cast(@p_syear as varchar) + ' between rgr.start_dt and rgr.end_dt or (rgr.end_dt is null and rgr.start_dt <= ' + cast(@p_syear as varchar) + '))
		        and (' + cast(@p_syear as varchar) + ' between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= ' + cast(@p_syear as varchar) + '))
		    ),
		    escores as (
		      select
		        student_id,
		        up.rs,
		        up.score
		      from (
		        select * from ' + @v_t2_tbl_nm + ') t2
		        unpivot (
		          score for rs in (';
		    set @cnt = 1;
		    set @ycnt = 1;
		    set @rr_counter = (select count(*) from #rr_data where rule_order = 1);
		    while @cnt <= @rr_counter
		    begin
		      set @sub_rr_counter = (select max(rule_order) from #rr_data where substring(title, len(title), 1) = cast(@cnt as varchar));
		      while @ycnt <= @sub_rr_counter
		      begin
		        if @cnt = 1 and @ycnt = 1
		          set @v_sql = @v_sql + 'rs' + cast(@cnt as varchar) + '_' + cast(@ycnt as varchar)
		        else
		          set @v_sql = @v_sql + ', rs' + cast(@cnt as varchar) + '_' + cast(@ycnt as varchar)
		        select @ycnt += 1;
		      end
		      select @cnt += 1;
		      set @ycnt = 1;
		    end
		    set @v_sql = @v_sql + ')
		        ) up
		    ),
		    err_item as (
		      select
		        es.student_id,
		        ei.imm_error_msg,
		        (select stuff((select concat('', '', error_msg) from eitems where rs = es.rs for xml path('''')), 1, 1, '''')) error_msg
		      from escores es
		      join eitems ei
		        on ei.rs = es.rs
		      join #t3e t3e
		        on t3e.rs = substring(lower(es.rs), 0, 3) and t3e.rank = 1
		      where es.score = 0
		      group by es.student_id, ei.imm_error_msg, es.rs
		    )
		    insert into #err_item (student_id, imm_error_msg, error_msg)
		    select * from err_item';

		    if @p_debug <> 1
		      -- Run the query.
		      execute (@v_sql)
		    else
		      -- Return the query.
		      print '5.5' + char(10) + @v_sql + char(10) + char(10);

		  end;

		  set @v_sql = '
		  with vars as (
		    select ' + cast(@v_imm_col_id as varchar) + ' cfid
		  )
		  , imm as (
		    select
		      cast (cfso.id as varchar) id,
		      cfso.code,
		      cfso.label,
		      i.title as vaccine_class
		    from custom_field_select_options cfso
		    join imm_immunizations i
		      on lower(i.code) = lower(cfso.code)
		    where cfso.source_id = (select cast(code as int) from imm_config where title = ''IMM_VAC_SOURCE_ID'')
		  )
		  , exempt as (
				select distinct
					cfle.source_id as student_id,
					i.title as vaccine_class,
					max(code.label) as exemption
			  from custom_field_log_entries cfle
				join custom_field_select_options vaccine
		      on cast(vaccine.id as varchar) = cfle.log_field1
		    join imm_immunizations i
		      on lower(i.code) = lower(vaccine.code)
				join custom_field_select_options code
		      on cast(code.id as varchar) = cfle.log_field2
				where vaccine.source_id = (select cast(code as int) from imm_config where title = ''IMM_EXEMPT_IMM_SOURCE_ID'')
		      and code.source_id = (select cast(code as int) from imm_config where title = ''IMM_EXEMPT_EXEMPT_SOURCE_ID'')
				  and cfle.field_id = (select cast(code as int) from imm_config where title = ''IMM_EXEMPT_FIELD_ID'')
		      and isdate(substring(cfle.log_field3, 0, 50)) = 1
		      and isdate(substring(cfle.log_field4, 0, 50)) = 1 ' +
				  @v_added_filter
				  + ' and ( code.code in (''LIF'', ''DIS'')
				      or getdate() between cast(cfle.log_field3 as date) and coalesce(cast(cfle.log_field4 as date), getdate())
				    )
				group by cfle.source_id, i.title
		  ),
		  comp_rec as (
		    select
		      count(*) c,
		      source_id student_id
		    from custom_field_log_entries cfle
		    where cfle.source_class = ''SISStudent''
		      and cfle.field_id = (select cfid from vars)
		      and cfle.log_field1 = (select id from imm where lower(code) = ''' + @p_immunization + ''')'
		    + @v_added_filter +
		    ' group by source_id
		  )
		  insert into #comp_data (student_id, vaccine_id, comp, message, exempt, custom_field_id, action)
		    select distinct
		      t3.student_id,
		      (select id from imm where lower(code) = ''' + @p_immunization + ''') as vaccine_id,
		      case when e.vaccine_class = ''' + (select title from imm_immunizations where id = @v_imm_id) + ''' and e.exemption is not null then ''Yes''
		  ';

		  -- Reset counter.
		  set @v_rs_counter = 0;

		  begin
		    set @cr = cursor for select * from #rr_data where rule_order = 1;
		    open @cr;
		    fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    while @@FETCH_STATUS = 0
		    begin
		      set @v_rs_counter = @v_rs_counter + 1;
		      set @v_sql = @v_sql + ' when t3.rs' + cast(@v_rs_counter as varchar) + ' = 100 then ''Yes'' ';
		      fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    end;
		  end;

		  set @v_sql = @v_sql + ' else ''No'' end as compliant,
		  case when e.vaccine_class = t3.vaccine_class and e.exemption is not null then ''Student is exempt.'' ';

		  -- Reset counter.
		  set @v_rs_counter = 0;

		  begin
		    set @cr = cursor for select * from #rr_data where rule_order = 1;
		    open @cr;
		    fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    while @@FETCH_STATUS = 0
		    begin
		      set @v_rs_counter = @v_rs_counter + 1;
		      set @v_default_error = @cr_t1error;
		      if @v_error_handling = 'Generic'
		        set @v_sql = @v_sql + ' when t3.rs' + cast(@v_rs_counter as varchar) + ' = 100 then ''' + @cr_t1comp + ''' '
		      else
		        set @v_sql = @v_sql + ' when t3.rs' + cast(@v_rs_counter as varchar) + ' = 100 then ''' + @cr_t2comp + ''' ';
		      fetch next from @cr into @cr_title, @cr_t1error, @cr_t2error, @cr_t3error, @cr_t1comp, @cr_t2comp, @cr_code, @cr_grade_limiter, @cr_operand, @cr_score, @cr_rule_order;
		    end;
		  end;

		  if @v_error_handling = 'Generic'
		    set @v_sql = @v_sql + ' else ''' + @v_default_error + ''' end as required_doses,'
		  else if @v_error_handling = 'Ruleset'
		    set @v_sql = @v_sql + ' else t3e.error_msg end as required_doses,'
		  else if @v_error_handling = 'Itemized'
		    set @v_sql = @v_sql + ' else concat(ei.imm_error_msg, '' '', ei.error_msg) end as required_doses,'
		  else
		    set @v_sql = @v_sql + ' else null end as required_doses,'

		  set @v_sql = @v_sql + ' case when e.exemption is null then ''No exemption'' else e.exemption end as exemption_msg,
		  (select cfid from vars) as custom_field_id,
		  case when (cr.c = 0 or cr.student_id is null) then ''i'' else ''u'' end as action
		  from ' + @v_t3_tbl_nm + ' t3';

		  if @v_error_handling <> 'Generic'
		    set @v_sql = @v_sql + ' join #t3e t3e
		      on t3e.student_id = t3.student_id and t3e.rank = 1';

		  if @v_error_handling = 'Itemized'
		    set @v_sql = @v_sql + ' left join #err_item ei
		      on ei.student_id = t3.student_id';

		  set @v_sql = @v_sql + ' left join exempt e
		    on e.student_id = t3.student_id and e.vaccine_class = t3.vaccine_class
		  left join comp_rec cr
		    on cr.student_id = t3.student_id';

		  if @p_debug <> 1
		    -- Run the query.
		    execute (@v_sql)
		  else
		    -- Return the query.
		    print '6.' + char(10) + @v_sql + char(10) + char(10);

		  insert into custom_field_log_entries (
		    id,
		    source_class,
		    source_id,
		    field_id,
		    log_field1,
		    log_field2,
		    log_field3,
		    log_field4,
		    log_field5)
		  select
		    next value for custom_field_log_entries_seq,
		    'SISStudent',
		    student_id,
		    custom_field_id,
		    vaccine_id,
		    comp,
		    message,
		    exempt,
		    action
		  from #comp_data
		  where action = 'i';

		  update
		    cfle
		  set
		    cfle.log_field2 = cd.comp,
		    cfle.log_field3 = cd.message,
		    cfle.log_field4 = cd.exempt,
		    cfle.log_field5 = cd.action
		  from custom_field_log_entries cfle
		  join #comp_data cd
		    on cd.student_id = cfle.source_id
		  where cfle.source_class = 'SISStudent'
		    and cfle.log_field1 = cd.vaccine_id
		    and (cfle.log_field2 <> cd.comp or cfle.log_field3 <> cd.message or cfle.log_field4 <> cd.exempt)
		    and cfle.field_id = cd.custom_field_id
		    and cd.action = 'u';

		  -- Exception to remove Florda Hep B2 or Hep B3 No's when the other is Yes.
		  begin
		    set @cr = cursor for with rgr as (
		      select distinct
		        rgr.immunization_id
		      from imm_ruleset_groups_rulesets rgr
		      where rgr.ruleset_group_id = @v_selected_ruleset_group_id
		        and (@p_syear between rgr.start_dt and rgr.end_dt or (rgr.end_dt is null and rgr.start_dt <= @p_syear))
		    ),
		    ret_recs as (
		      select
		        cfle.source_id,
		        max(case when log_field2 = 'No' then cfle.id else null end) cfle_id,
		        sum(case when log_field2 = 'Yes' then 1 else 0 end) as has_yes
		      from imm_immunizations i
		      join rgr
		        on rgr.immunization_id = i.id
		      join custom_field_select_options cfso
		        on lower(cfso.code) = i.code
		      join custom_field_log_entries cfle
		        on cfle.log_field1 = cast(cfso.id as varchar)
		      where i.code in ('hepb2', 'hepb3')
		        and cfso.source_id = (select cast(code as bigint) from imm_config where title = 'IMM_VAC_SOURCE_ID')
		      group by cfle.source_id
		    )
		    select
		      cfle_id,
		      source_id
		    from ret_recs
		    where cfle_id is not null
		      and has_yes > 0
		    open @cr;
		    fetch next from @cr into @cr_cfle_id, @cr_source_id;
		    while @@FETCH_STATUS = 0
		    begin
		      delete from custom_field_log_entries where id = @cr_cfle_id and source_id = @cr_source_id and source_class = 'SISStudent' and log_field2 = 'No';
		      fetch next from @cr into @cr_cfle_id, @cr_source_id;
		    end;
		  end;

		  -- Drop temp tables.
		  if object_id('tempdb..#shot_data') is not null
		    drop table #shot_data;

		  if object_id('tempdb..#rr_data') is not null
		    drop table #rr_data;

		  if object_id('tempdb..#t3e') is not null
		    drop table #t3e;

		  if object_id('tempdb..#err_item') is not null
		    drop table #err_item;

		  if object_id('tempdb..#comp_data') is not null
		    drop table #comp_data;

		  if object_id('tempdb..' + @v_t3_tbl_nm) is not null
		  begin
		    set @v_sql = 'drop table ' + @v_t3_tbl_nm;
		    exec (@v_sql);
		  end;

		end;",

		// 18. Procedures/sp_imm_calc_clean.sql
		"create procedure sp_imm_calc_clean(
		  @p_student_id bigint,
		  @p_syear int
		)
		as
		begin
		  set ansi_warnings off;
		  set nocount on;

		  declare
		    @v_imm_col_id int,
		    @v_syear int,
		    @v_selected_ruleset_group_id int;

			-- Select current syear to calculate against.
			if @p_syear is null
				set @v_syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null)
		  else
		    set @v_syear = @p_syear;

		  -- Catch to ensure the Dynamic Version for Immunizations system has all required variables set.
		  if (select
		      count(*)
		    from program_config
		    where title in (
		        'IMM_COMPLIANCE',
		        'IMM_SELECTED_RULESET_GROUP',
		        'IMM_ERROR_HANDLING',
		        'IMM_INTERVAL_DAYS'
		      )
		      and syear = @v_syear
		      and program = 'system'
		      and school_id is null
		      and value is null) > 0
		    return;

		  -- Check imm_config as well.
		  if (select
		      count(*)
		    from imm_config
		    where code is null) > 0
		    return;

		  -- Ensure Immunization Module is active.
		  if (select value from program_config where program = 'system' and title = 'IMM_COMPLIANCE' and syear = @v_syear and school_id is null) <> 'Y'
		    return;

			-- Pull the column id for the custom_field to update.
			set @v_imm_col_id = (select id from custom_fields where column_name = (select code from imm_config where title = 'IMM_CUSTOM_COL_NAME'));

			-- Pull the selected_ruleset_group from config table.
			set @v_selected_ruleset_group_id = (select id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = @v_syear and school_id is null));

			if @v_imm_col_id is null or @v_selected_ruleset_group_id is null
				return;

		  declare
		    @cr cursor,
		    @crID int,
		    @crSourceID int,
		    @crFieldID int,
		    @crCode varchar(100),
		    @crImmID int;

			if @p_student_id is null
		    begin
		      set @cr = cursor for
		  			select
		  				cfle.id,
		  				cfle.source_id,
		  				cfle.log_field1 field_id,
		  				cfso.code,
		  				rg.immunization_id
		  			from custom_field_log_entries cfle
		  			join custom_field_select_options cfso
		  				on cast(cfso.id as varchar) = cfle.log_field1
		  			join imm_immunizations i
		  				on i.code = lower(cfso.code)
		  			left join (
		  				select distinct
		  					rgr.immunization_id
		  				from imm_ruleset_groups_rulesets rgr
		  				where rgr.ruleset_group_id = @v_selected_ruleset_group_id) rg
		  				on rg.immunization_id = i.id
		  			where cfle.field_id = @v_imm_col_id
		  				and rg.immunization_id is null;
		  		open @cr;
		      fetch next from @cr into @crID, @crSourceID, @crFieldID, @crCode, @crImmID;
		      while @@FETCH_STATUS = 0
		      begin
		        delete from custom_field_log_entries where id = @crID and source_id = @crSourceID and source_class = 'SISStudent' and field_id = @crFieldID;
		        fetch next from @cr into @crID, @crSourceID, @crFieldID, @crCode, @crImmID;
		      end;
		    end;
			else
		    begin
				  set @cr = cursor for
		  			select
		  				cfle.id,
		  				cfle.source_id,
		  				cfle.log_field1 field_id,
		  				cfso.code,
		  				rg.immunization_id
		  			from custom_field_log_entries cfle
		  			join custom_field_select_options cfso
		  				on cast(cfso.id as varchar) = cfle.log_field1
		  			join imm_immunizations i
		  				on i.code = lower(cfso.code)
		  			left join (
		  				select distinct
		  					rgr.immunization_id
		  				from imm_ruleset_groups_rulesets rgr
		  				where rgr.ruleset_group_id = @v_selected_ruleset_group_id) rg
		  				on rg.immunization_id = i.id
		  			where cfle.field_id = @v_imm_col_id
		  				and cfle.source_id = @p_student_id
		  				and rg.immunization_id is null;
		  		open @cr;
		      fetch next from @cr into @crID, @crSourceID, @crFieldID, @crCode, @crImmID;
		      while @@FETCH_STATUS = 0
		      begin
		        delete from custom_field_log_entries where id = @crID and source_id = @crSourceID and source_class = 'SISStudent' and field_id = @crFieldID;
		        fetch next from @cr into @crID, @crSourceID, @crFieldID, @crCode, @crImmID;
		      end;
		    end;
		end;",

		// 19. Procedures/sp_imm_run_all_comp.sql
		"create procedure sp_imm_run_all_comp (
			@p_student_id bigint,
			@p_syear int
		)
		as
		begin
		  set ansi_warnings off;
		  set nocount on;

			if @p_student_id is null
			begin
				declare
			    @cr cursor,
					@cr_student_id bigint;

				begin
			    set @cr = cursor for select distinct student_id from student_enrollment where syear = @p_syear;
			    open @cr;
			    fetch next from @cr into @cr_student_id;
			    while @@FETCH_STATUS = 0
			    begin
		    		exec sp_imm_calc 'dtp', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'hepa', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'hepb', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'ms', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'men', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'mu', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'pol', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'rub', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'var', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'hib', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'pnc', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'hepb2', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc 'hepb3', @cr_student_id, @p_syear, 0;
						exec sp_imm_calc_clean @cr_student_id, @p_syear;
			      fetch next from @cr into @cr_student_id;
			    end;
			  end;
			end;
			else
			begin
				exec sp_imm_calc 'dtp', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'hepa', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'hepb', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'ms', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'men', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'mu', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'pol', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'rub', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'var', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'hib', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'pnc', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'hepb2', @p_student_id, @p_syear, 0;
				exec sp_imm_calc 'hepb3', @p_student_id, @p_syear, 0;
				exec sp_imm_calc_clean @p_student_id, @p_syear;
			end;
		end;",

		// 20. Procedures/sp_imm_uninstall.sql
		"create procedure sp_imm_uninstall
		as
		begin
		  set ansi_warnings off;
		  set nocount on;

			drop procedure sp_imm_run_all_comp;
			drop procedure sp_imm_calc_clean;
			drop procedure sp_imm_calc;
			drop procedure sp_imm_calc_data;
			drop procedure sp_imm_get_rulesets;

			drop type IMMCalcDataType;
			drop type IMMGetRulesetsType;

			drop table imm_ruleset_groups_rulesets;
			drop table imm_vaccines;
			drop table imm_rulesets_rules_dynamic_values;
			drop table imm_rulesets_rules;
			drop table imm_rulesets;
			drop table imm_ruleset_groups;
			drop table imm_rules;
			drop table imm_immunizations;
			drop table imm_dynamic_values;
			drop table imm_config;

			delete from program_config where lower(title) like 'imm_%';
			delete from custom_fields_join_categories where field_id in (select id from custom_fields where alias in ('immunization_compliance', 'immunization_exemption'));
			delete from permission where \"key\" like concat('SISStudent:', (select id from custom_fields where alias = 'immunization_compliance'), '%') and (select count(id) from custom_fields where alias = 'immunization_compliance') = 1;
			delete from permission where \"key\" like concat('SISStudent:', (select id from custom_fields where alias = 'immunization_exemption'), '%') and (select count(id) from custom_fields where alias = 'immunization_exemption') = 1;
			delete from edit_rule_criteria where rule_id in (select id from edit_rules where lower(name) like 'imm -%');
			delete from edit_rules where lower(name) like 'imm -%';
			delete from custom_field_log_entries where field_id in (select id from custom_fields where alias in ('immunization_compliance', 'immunization_exemption'));
			delete from custom_field_select_options where source_class = 'CustomFieldLogColumn' and source_id in (select id from custom_field_log_columns where type = 'select' and field_id in (select id from custom_fields where alias in ('immunization_compliance', 'immunization_exemption')));
			delete from custom_field_log_columns where field_id in (select id from custom_fields where alias in ('immunization_compliance', 'immunization_exemption'));
			delete from custom_fields where alias in ('immunization_compliance', 'immunization_exemption');
			delete from custom_reports where title = 'Compliance Report' and parent_id = (select id from custom_reports_folders where title = 'Immunizations');
			delete from custom_reports_folders where title = 'Immunizations';
			delete from custom_reports_variables where variable_name like 'IMM_%';

			delete from cron_jobs where class = 'ImmunizationsNightlyJob';

			update database_migrations set deleted = '1' where migration_id = 'FOCUS-13030' and deleted is null;

		end;"
  ];

} else if (Database::$type === 'postgres') {
	// 5. Projects/Immunizations/Dynamic Version/PostgreSQL
  $sql = [
		// 1. Design Model/imm_objects.sql
    "CREATE TABLE imm_config (
			title                varchar  NOT NULL,
			code                 varchar  ,
			CONSTRAINT pk_config_title PRIMARY KEY ( title )
		 );",
		"CREATE TABLE imm_dynamic_values (
			id                   serial  NOT NULL,
			dv_type              varchar  NOT NULL,
			dv_value             varchar  NOT NULL,
			CONSTRAINT pk_dynamic_values PRIMARY KEY ( id ),
			CONSTRAINT dynamic_values_uk_i UNIQUE ( dv_type, dv_value )
		 );",
		"CREATE TABLE imm_immunizations (
			id                   serial  NOT NULL,
			title                varchar  ,
			code                 varchar  ,
			CONSTRAINT pk_immunizations PRIMARY KEY ( id )
		 );",
		"CREATE TABLE imm_rules (
			id                   serial  NOT NULL,
			code                 varchar  NOT NULL,
			CONSTRAINT pk_rules PRIMARY KEY ( id ),
			CONSTRAINT rules_uk_i UNIQUE ( code )
		 );",
		"CREATE TABLE imm_ruleset_groups (
			id                   serial  NOT NULL,
			title                varchar  ,
			description          varchar  ,
			CONSTRAINT pk_ruleset_groups_0 PRIMARY KEY ( id )
		 );",
		"CREATE TABLE imm_rulesets (
			id                   serial  NOT NULL,
			title                varchar  ,
			compliance_msg       varchar  ,
			error_msg            varchar  ,
			CONSTRAINT pk_rulesets PRIMARY KEY ( id )
		 );",
		"CREATE TABLE imm_rulesets_rules (
			id                   serial  NOT NULL,
			ruleset_id           integer  ,
			rule_id              integer  ,
			rule_order           integer  ,
			start_dt             integer  ,
			end_dt               integer  ,
			grade_limiter        varchar(1)  ,
			operand              varchar  ,
			score                varchar  ,
			error_msg            varchar  ,
			CONSTRAINT pk_rulesets_rules PRIMARY KEY ( id )
		 );",
		"CREATE INDEX idx_rulesets_rules ON imm_rulesets_rules ( ruleset_id );",
		"CREATE INDEX idx_rulesets_rules_0 ON imm_rulesets_rules ( rule_id );",
		"CREATE TABLE imm_rulesets_rules_dynamic_values (
			rulesets_rules_id    integer  ,
			dynamic_value_id     integer  ,
			value_order          integer
		 );",
		"CREATE INDEX idx_rulesets_rules_dynamic_values ON imm_rulesets_rules_dynamic_values ( rulesets_rules_id );",
		"CREATE INDEX idx_rulesets_rules_dynamic_values_0 ON imm_rulesets_rules_dynamic_values ( dynamic_value_id );",
		"CREATE TABLE imm_vaccines (
			immunization_id      integer  ,
			ruleset_group_id     integer  ,
			title                varchar  NOT NULL,
			code                 varchar  NOT NULL
		 );",
		"CREATE INDEX idx_imm_vaccines_immunization_id ON imm_vaccines ( immunization_id );",
		"CREATE INDEX idx_imm_vaccines ON imm_vaccines ( ruleset_group_id );",
		"CREATE TABLE imm_ruleset_groups_rulesets (
			ruleset_group_id     integer  ,
			ruleset_id           integer  ,
			immunization_id      integer  ,
			imm_order            integer  ,
			start_dt             integer  ,
			end_dt               integer  ,
			compliance_msg       varchar  ,
			error_msg            varchar
		 );",
		"CREATE INDEX idx_ruleset_groups_rulesets_immunization_id ON imm_ruleset_groups_rulesets ( immunization_id );",
		"CREATE INDEX idx_ruleset_groups_rulesets_ruleset_group_id ON imm_ruleset_groups_rulesets ( ruleset_group_id );",
		"CREATE INDEX idx_ruleset_groups_rulesets_ruleset_id ON imm_ruleset_groups_rulesets ( ruleset_id );",
		"ALTER TABLE imm_ruleset_groups_rulesets ADD CONSTRAINT fk_ruleset_groups_rulesets_immunization_id FOREIGN KEY ( immunization_id ) REFERENCES imm_immunizations( id );",
		"ALTER TABLE imm_ruleset_groups_rulesets ADD CONSTRAINT fk_ruleset_groups_rulesets_ruleset_group_id FOREIGN KEY ( ruleset_group_id ) REFERENCES imm_ruleset_groups( id );",
		"ALTER TABLE imm_ruleset_groups_rulesets ADD CONSTRAINT fk_ruleset_groups_rulesets_ruleset_id FOREIGN KEY ( ruleset_id ) REFERENCES imm_rulesets( id );",
		"ALTER TABLE imm_rulesets_rules ADD CONSTRAINT fk_rulesets_rules_rulesets FOREIGN KEY ( ruleset_id ) REFERENCES imm_rulesets( id );",
		"ALTER TABLE imm_rulesets_rules ADD CONSTRAINT fk_rulesets_rules_rules FOREIGN KEY ( rule_id ) REFERENCES imm_rules( id );",
		"ALTER TABLE imm_rulesets_rules_dynamic_values ADD CONSTRAINT fk_rulesets_rules_dynamic_values FOREIGN KEY ( rulesets_rules_id ) REFERENCES imm_rulesets_rules( id );",
		"ALTER TABLE imm_rulesets_rules_dynamic_values ADD CONSTRAINT fk_rulesets_rules_dynamic_values1 FOREIGN KEY ( dynamic_value_id ) REFERENCES imm_dynamic_values( id );",
		"ALTER TABLE imm_vaccines ADD CONSTRAINT fk_imm_vaccines_immunization_id FOREIGN KEY ( immunization_id ) REFERENCES imm_immunizations( id );",
		"ALTER TABLE imm_vaccines ADD CONSTRAINT fk_imm_vaccines_ruleset_groups FOREIGN KEY ( ruleset_group_id ) REFERENCES imm_ruleset_groups( id );",

		// 2. ../Generic Loadout/program_config.sql
		"insert into program_config (syear, program, title, value) values (
		  (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null),
		  'system',
		  'IMM_COMPLIANCE',
		  'N'
		);",
		"insert into program_config (syear, program, title, value) values (
		  (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null),
		  'system',
		  'IMM_SELECTED_RULESET_GROUP',
		  'Florida'
		);",
		"insert into program_config (syear, program, title, value) values (
		  (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null),
		  'system',
		  'IMM_ERROR_HANDLING',
		  'Ruleset'
		);",
		"insert into program_config (syear, program, title, value) values (
		  (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null),
		  'system',
		  'IMM_INTERVAL_DAYS',
		  '4'
		);",

		// 3. Default Loadout/imm_config.sql
		"insert into imm_config (title, code) values (
		  'IMM_CUSTOM_COL_NAME',
		  (select column_name from custom_fields where alias = 'immunization_compliance' and type = 'log' and source_class = 'SISStudent' and deleted is null)
		);",
		"insert into imm_config (title, code) values (
		  'IMM_DATA_COLUMNS',
		  (select string_agg(concat('cfle.', lower(column_name)), ', ') from custom_field_log_columns where field_id = (select id from custom_fields where legacy_id in (400000929, 929) and type = 'log' and source_class = 'SISStudent' and deleted is null) and type = 'date' and title ~ '[0-9]' and deleted is null)
		);",
		"insert into imm_config (title, code) values (
		  'IMM_SHOTS_FIELD_ID',
		  (select id from custom_fields where legacy_id in (400000929, 929) and type = 'log' and source_class = 'SISStudent' and deleted is null)
		);",
		"insert into imm_config (title, code) values (
		  'IMM_VAC_SOURCE_ID',
		  (select id from custom_field_log_columns where field_id = (select id from custom_fields where alias = 'immunization_compliance' and type = 'log' and source_class = 'SISStudent' and deleted is null) and type = 'select')
		);",
		"insert into imm_config (title, code) values (
		  'IMM_EXEMPT_FIELD_ID',
		  (select id from custom_fields where alias = 'immunization_exemption' and type = 'log' and source_class = 'SISStudent' and deleted is null)
		);",

		// 4. ../Generic Loadout/imm_immunizations.sql
		"insert into imm_immunizations (title, code) values ('DTaP', 'dtp');",
		"insert into imm_immunizations (title, code) values ('Hep A', 'hepa');",
		"insert into imm_immunizations (title, code) values ('Hep B', 'hepb');",
		"insert into imm_immunizations (title, code) values ('HIB', 'hib');",
		"insert into imm_immunizations (title, code) values ('Measles', 'ms');",
		"insert into imm_immunizations (title, code) values ('Meningococcal', 'men');",
		"insert into imm_immunizations (title, code) values ('Mumps', 'mu');",
		"insert into imm_immunizations (title, code) values ('PNC', 'pnc');",
		"insert into imm_immunizations (title, code) values ('Polio', 'pol');",
		"insert into imm_immunizations (title, code) values ('Rubella', 'rub');",
		"insert into imm_immunizations (title, code) values ('Varicella', 'var');",
		"insert into imm_immunizations (title, code) values ('Hep B2', 'hepb2');",
		"insert into imm_immunizations (title, code) values ('Hep B3', 'hepb3');",

		// 5. ../Generic Loadout/imm_ruleset_groups.sql
		"insert into imm_ruleset_groups (title, description) values ('Texas','Rules from Texas.');",
		"insert into imm_ruleset_groups (title, description) values ('Florida','Rules from Florida.');",

		// 6. ../Generic Loadout/imm_vaccines.sql
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'PNC'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pneumococcal23', 'P23');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Diphtheria-Tetanus-Pertussis', 'DTP');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Diphtheria-Tetanus-Acellular-Pertussis', 'DTaP');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Tetanus-Diphtheria', 'Td');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Diphtheria-Tetanus', 'DT');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep A'), (select id from imm_ruleset_groups where title = 'Texas'), 'Hepatitis A', 'HepA');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep B'), (select id from imm_ruleset_groups where title = 'Texas'), 'Hepatitis B', 'HepB');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Texas'), 'Haemophilus influenza type b', 'Hib');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Rubella'), (select id from imm_ruleset_groups where title = 'Texas'), 'Measles-Mumps-Rubella', 'MMR');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Mumps'), (select id from imm_ruleset_groups where title = 'Texas'), 'Measles-Mumps-Rubella', 'MMR');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Texas'), 'Measles-Mumps-Rubella', 'MMR');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'Meningococcal', 'MN');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'Meningococcal B, recombinant', 'MNB');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'PNC'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pneumococcal Conjugate', 'PC');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Inactivated Polio', 'PV');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Oral Polio', 'OPV');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Tdap', 'Tdap');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Varicella'), (select id from imm_ruleset_groups where title = 'Texas'), 'Varicella', 'VA');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Texas'), 'Measles', 'Measles');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'MCV4', 'MCV4');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'MenACWY', 'MenACWY');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'Menveo', 'Menveo');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'Menactra', 'Menactra');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Kinrix', 'Kinrix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Kinrix', 'Kinrix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pediarix', 'Pediarix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep B'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pediarix', 'Pediarix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pediarix', 'Pediarix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pentacel', 'Pentacel');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pentacel', 'Pentacel');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pentacel', 'Pentacel');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Texas'), 'Comvax', 'Comvax');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Meningococcal'), (select id from imm_ruleset_groups where title = 'Texas'), 'MenHibrix', 'MenHibrix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Texas'), 'MenHibrix', 'MenHibrix');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Varicella'), (select id from imm_ruleset_groups where title = 'Texas'), 'ProQuad', 'ProQuad');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Rubella'), (select id from imm_ruleset_groups where title = 'Texas'), 'ProQuad', 'ProQuad');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Mumps'), (select id from imm_ruleset_groups where title = 'Texas'), 'ProQuad', 'ProQuad');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Texas'), 'ProQuad', 'ProQuad');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'PNC'), (select id from imm_ruleset_groups where title = 'Texas'), 'Pneumococcal13 ', 'PCV13');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'DTP vaccine', 'A');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'DT (Pediatric) vaccine', 'B');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'Tdap (Tetanus-Diphtheria-Pertussis) vaccine', 'P');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'Td (Tetanus-Diphtheria) vaccine', 'Q');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep B3'), (select id from imm_ruleset_groups where title = 'Florida'), 'Hepatitis B vaccine - 3 doses', 'J');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'HIB'), (select id from imm_ruleset_groups where title = 'Florida'), 'Hib Haemophilus Influenza Type B vaccine', 'E');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Rubella'), (select id from imm_ruleset_groups where title = 'Florida'), 'MMR (Measles, Mumps and Rubella) vaccine', 'F');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Mumps'), (select id from imm_ruleset_groups where title = 'Florida'), 'MMR (Measles, Mumps and Rubella) vaccine', 'F');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Florida'), 'MMR (Measles, Mumps and Rubella) vaccine', 'F');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Measles'), (select id from imm_ruleset_groups where title = 'Florida'), 'Measles (Rubeola) vaccine', 'G');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Mumps'), (select id from imm_ruleset_groups where title = 'Florida'), 'Mumps vaccine', 'H');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Rubella'), (select id from imm_ruleset_groups where title = 'Florida'), 'Rubella vaccine', 'I');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'PNC'), (select id from imm_ruleset_groups where title = 'Florida'), 'Pneumococcal Conjugate vaccine', 'N');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Polio'), (select id from imm_ruleset_groups where title = 'Florida'), 'Polio vaccine', 'D');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Varicella'), (select id from imm_ruleset_groups where title = 'Florida'), 'VZV (Varicella) vaccine', 'K');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Varicella'), (select id from imm_ruleset_groups where title = 'Florida'), 'Varicella disease', 'L');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'DTaP'), (select id from imm_ruleset_groups where title = 'Florida'), 'Td Adult vaccine or Tdap vaccine', 'C');",
		"insert into imm_vaccines (immunization_id, ruleset_group_id, title, code) values ((select id from imm_immunizations where title = 'Hep B2'), (select id from imm_ruleset_groups where title = 'Florida'), 'Hepatitis B vaccine - 2 doses', 'M');",

		// 7. ../Generic Loadout/imm_rulesets.sql
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS1', '5 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS2', '4 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS3', '3 doses, 1 on or after 4th birthday, and student is at least 7 years old.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS4', '3 doses, 1 booster within last 5 years, and student is in 7th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:DTaP:RS5', '3 doses, 1 booster within last 10 years, and student is in 8th - 12th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Polio:RS1', '4 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Polio:RS2', '3 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Measles:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Measles:RS2', '2 doses and full MMR series happened before 2009.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Mumps:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Mumps:RS2', '1 dose and full MMR series happened before 2009.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Rubella:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Rubella:RS2', '1 dose and full MMR series happened before 2009.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Hep B:RS1', '3 doses.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Hep B:RS2', '2 doses of vaccine Recombivax recieved between 11 and 15 years of age.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Varicella:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Meningococcal:RS1', 'Not required for PK - 6th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Meningococcal:RS2', '1 dose on or after 10th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Hep A:RS1', '2 doses, 1 on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:Hep A:RS2', 'Not required for 9th - 12th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:HIB:RS1', 'Not required after PK or after a student turns 5.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:HIB:RS2', '1 dose, on or after 15 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:HIB:RS3', '3 or 4 doses, 1 dose on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:PNC:RS1', 'Not required after PK or after a student turns 5.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:PNC:RS2', '1 dose, on or after 24 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:PNC:RS3', '2 doses, on or after 12 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Texas:PNC:RS4', '2 or 3 doses on or before 12 months, 1 dose on or after 12 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS1', '5 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS2', '5 doses, 1 on or before 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS3', '4 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS4', '4 doses of Pediatric Diphtheria-Tetanus (DT) as substitute for DTP/DTaP due to Pertussis exemption.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS5', '3 doses of Adult Tetanus-Diphtheria (Td) with a Tdap dose replacing 1 dose.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS6', '3 doses, 1 booster within last 5 years, and student is in 7th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:DTaP:RS7', '3 doses, 1 booster within last 10 years, and student is in 8th - 12th grade.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Polio:RS1', '4 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Polio:RS2', '5 doses, when 4th dose before 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Polio:RS3', '3 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Polio:RS4', '3 doses, all after 7th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Measles:RS1', '2 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Mumps:RS1', '2 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Rubella:RS1', '2 doses, 1 on or after 4th birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:HIB:RS1', 'Not required after PK or after a student turns 5.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:HIB:RS2', '1 dose, on or after 15 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:HIB:RS3', '3 or 4 doses, 1 dose on or after 1st birthday.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Hep B2:RS1', '2 doses between 11 and 15 years of age.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Hep B2:RS2', 'Not required in before KG.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Hep B3:RS1', '3 doses.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Varicella:RS1', '1 dose.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Varicella:RS2', '2 doses.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:Varicella:RS3', 'Serological confirmation.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:PNC:RS1', 'Not required after PK or after a student turns 5.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:PNC:RS2', '1 dose, on or after 24 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:PNC:RS3', '2 doses, on or after 12 months.', null);",
		"insert into imm_rulesets (title, compliance_msg, error_msg) values ('Florida:PNC:RS4', '2 or 3 doses on or before 12 months, 1 dose on or after 12 months.', null);",

		// 8. ../Generic Loadout/imm_ruleset_groups_rulesets.sql
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS1'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS2'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS4'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS5'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Polio:RS1'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Polio:RS2'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Measles:RS1'),
		  (select id from imm_immunizations where title = 'Measles'),
		  3,
		  1970,
			null,
			'Measles compliance complete.',
		  'Measles compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Measles:RS2'),
		  (select id from imm_immunizations where title = 'Measles'),
		  3,
		  1970,
			null,
			'Measles compliance complete.',
		  'Measles compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS1'),
		  (select id from imm_immunizations where title = 'Mumps'),
		  4,
		  1970,
			null,
			'Mumps compliance complete.',
		  'Mumps compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS2'),
		  (select id from imm_immunizations where title = 'Mumps'),
		  4,
		  1970,
			null,
			'Mumps compliance complete.',
		  'Mumps compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS1'),
		  (select id from imm_immunizations where title = 'Rubella'),
		  5,
		  1970,
			null,
			'Rubella compliance complete.',
		  'Rubella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS2'),
		  (select id from imm_immunizations where title = 'Rubella'),
		  5,
		  1970,
			null,
			'Rubella compliance complete.',
		  'Rubella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Hep B:RS1'),
		  (select id from imm_immunizations where title = 'Hep B'),
		  6,
		  1970,
			null,
			'Hep B compliance complete.',
		  'Hep B compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Hep B:RS2'),
		  (select id from imm_immunizations where title = 'Hep B'),
		  6,
		  1970,
			null,
			'Hep B compliance complete.',
		  'Hep B compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Varicella:RS1'),
		  (select id from imm_immunizations where title = 'Varicella'),
		  7,
		  1970,
			null,
			'Varicella compliance complete.',
		  'Varicella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS1'),
		  (select id from imm_immunizations where title = 'Meningococcal'),
		  8,
		  1970,
			null,
			'Meningococcal compliance complete.',
		  'Meningococcal compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2'),
		  (select id from imm_immunizations where title = 'Meningococcal'),
		  8,
		  1970,
			null,
			'Meningococcal compliance complete.',
		  'Meningococcal compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS1'),
		  (select id from imm_immunizations where title = 'Hep A'),
		  9,
		  1970,
			null,
			'Hep A compliance complete.',
		  'Hep A compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS2'),
		  (select id from imm_immunizations where title = 'Hep A'),
		  9,
		  1970,
			null,
			'Hep A compliance complete.',
		  'Hep A compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:HIB:RS1'),
		  (select id from imm_immunizations where title = 'HIB'),
		  10,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:HIB:RS2'),
		  (select id from imm_immunizations where title = 'HIB'),
		  10,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:HIB:RS3'),
		  (select id from imm_immunizations where title = 'HIB'),
		  10,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:PNC:RS1'),
		  (select id from imm_immunizations where title = 'PNC'),
		  11,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:PNC:RS2'),
		  (select id from imm_immunizations where title = 'PNC'),
		  11,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:PNC:RS3'),
		  (select id from imm_immunizations where title = 'PNC'),
		  11,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Texas'),
		  (select id from imm_rulesets where title = 'Texas:PNC:RS4'),
		  (select id from imm_immunizations where title = 'PNC'),
		  11,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS1'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS2'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS3'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS4'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS5'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS6'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS7'),
		  (select id from imm_immunizations where title = 'DTaP'),
		  1,
		  1970,
			null,
			'DTaP compliance complete.',
		  'DTaP compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Polio:RS1'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Polio:RS2'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Polio:RS3'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Polio:RS4'),
		  (select id from imm_immunizations where title = 'Polio'),
		  2,
		  1970,
			null,
			'Polio compliance complete.',
		  'Polio compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Measles:RS1'),
		  (select id from imm_immunizations where title = 'Measles'),
		  3,
		  1970,
			null,
			'Measles compliance complete.',
		  'Measles compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Mumps:RS1'),
		  (select id from imm_immunizations where title = 'Mumps'),
		  4,
		  1970,
			null,
			'Mumps compliance complete.',
		  'Mumps compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Rubella:RS1'),
		  (select id from imm_immunizations where title = 'Rubella'),
		  5,
		  1970,
			null,
			'Rubella compliance complete.',
		  'Rubella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:HIB:RS1'),
		  (select id from imm_immunizations where title = 'HIB'),
		  6,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:HIB:RS2'),
		  (select id from imm_immunizations where title = 'HIB'),
		  6,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:HIB:RS3'),
		  (select id from imm_immunizations where title = 'HIB'),
		  6,
		  1970,
			null,
			'HIB compliance complete.',
		  'HIB compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Hep B2:RS1'),
		  (select id from imm_immunizations where title = 'Hep B2'),
		  7,
		  1970,
			null,
			'Hep B2 compliance complete.',
		  'Hep B2 compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Hep B2:RS2'),
		  (select id from imm_immunizations where title = 'Hep B2'),
		  7,
		  1970,
			null,
			'Hep B2 compliance complete.',
		  'Hep B2 compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Hep B3:RS1'),
		  (select id from imm_immunizations where title = 'Hep B3'),
		  8,
		  1970,
			null,
			'Hep B3 compliance complete.',
		  'Hep B3 compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS1'),
		  (select id from imm_immunizations where title = 'Varicella'),
		  9,
		  1970,
			null,
			'Varicella compliance complete.',
		  'Varicella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS2'),
		  (select id from imm_immunizations where title = 'Varicella'),
		  9,
		  1970,
			null,
			'Varicella compliance complete.',
		  'Varicella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS3'),
		  (select id from imm_immunizations where title = 'Varicella'),
		  9,
		  1970,
			null,
			'Varicella compliance complete.',
		  'Varicella compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:PNC:RS1'),
		  (select id from imm_immunizations where title = 'PNC'),
		  10,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:PNC:RS2'),
		  (select id from imm_immunizations where title = 'PNC'),
		  10,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:PNC:RS3'),
		  (select id from imm_immunizations where title = 'PNC'),
		  10,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",
		"insert into imm_ruleset_groups_rulesets (ruleset_group_id, ruleset_id, immunization_id, imm_order, start_dt, end_dt, compliance_msg, error_msg) values (
		  (select id from imm_ruleset_groups where title = 'Florida'),
		  (select id from imm_rulesets where title = 'Florida:PNC:RS4'),
		  (select id from imm_immunizations where title = 'PNC'),
		  10,
		  1970,
			null,
			'PNC compliance complete.',
		  'PNC compliance incomplete.');",

		// 9. Default Loadout/imm_rules.sql
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and current_date + days_interval >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv3:'' as date) and cast((dob + days_interval) + interval '':dv4:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)');",
		"insert into imm_rules (code) values ('sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv2:'' as date) and cast((dob + days_interval) + interval '':dv3:'' as date) then 1 else 0 end)');",

		// 10. ../Generic Loadout/imm_dynamic_values.sql
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'DTaP');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '4 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('int', '5');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('int', '4');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('int', '3');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '7 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''07''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''08'', ''09'', ''10'', ''11'', ''12''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''Tdap'', ''Td''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '5 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '10 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Polio');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Measles');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('int', '2');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '1 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '12/31/2008');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Mumps');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Rubella');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Hep B');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Recombivax');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '11 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '15 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Varicella');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Meningococcal');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''07'', ''08'', ''09'', ''10'', ''11'', ''12''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Hep A');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''09'', ''10'', ''11'', ''12''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'HIB');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''EE'', ''PK''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '15 months');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'PNC');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '2 years');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'B');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''Q'', ''P''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''KG''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Hep B2');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Hep B3');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''PK'', ''10'', ''11'', ''12''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', '''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08'', ''09''');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'L');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'Q');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'P');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 4');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 10');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 1');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'month, 15');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 2');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 7');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 5');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 11');",
		"insert into imm_dynamic_values (dv_type, dv_value) values ('chr', 'year, 15');",

		// 11. Default Loadout/imm_rulesets_rules.sql
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 5 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and current_date + days_interval >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  4,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Student must be at least 7 years old.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 booster within last 5 years.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 booster within last 10 years.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Polio:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4 doses with 1 being on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Polio:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses with 1 being on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Measles:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses with 1 being on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Measles:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses of Measles after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Measles:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Mumps after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Measles:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Rubella after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses with 1 being on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses of Measles after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Mumps after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Mumps:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Rubella after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses with 1 being on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses of Measles after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Mumps after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Rubella:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose of Rubella after 1st birthday and before 2009.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep B:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep B:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv3:'' as date) and cast((dob + days_interval) + interval '':dv4:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses of vaccine Recombivax recieved between 11 and 15 years of age.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Varicella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses, 1 dose on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 10th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:Hep A:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:HIB:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Not required after PK or after a student turns 5.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:HIB:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 15 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:HIB:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires at least 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:HIB:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Not required after PK or after a student turns 5.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requres 1 dose on or after 24 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses on or after 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires at least 2 doses on or before 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Texas:PNC:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires a shot on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 5 doses with the 5th dose on or before 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4 doses on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '4',
		  'Requires 4 doses of DT.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '=',
		  '0',
		  'Student must start DTaP immunization after age 7 to comply with this rule.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses of Td.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS5'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  '>=',
		  '1',
		  'Requires 1 dose of Tdap to replace Td.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS6'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS6'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS6'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 booster within last 5 years.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS7'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS7'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:DTaP:RS7'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 booster within last 10 years.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires dose after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 4th dose before 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  3,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 5 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3rd dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Polio:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '3',
		  'Requires 3rd dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Measles:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Measles:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Mumps:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Mumps:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Rubella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Rubella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 4th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:HIB:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Not required after PK or after a student turns 5.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:HIB:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 15 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:HIB:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires at least 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:HIB:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 1st birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Hep B2:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv2:'' as date) and cast((dob + days_interval) + interval '':dv3:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires 2 doses between 11th and 15th birthday.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Hep B2:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Hep B3:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 3 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  null,
		  null,
		  null
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'Y',
		  '>=',
		  '2',
		  'Requires 2 doses.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:Varicella:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires serological confirmation.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS1'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Not required after PK or after a student turns 5.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS2'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requres 1 dose on or after 24 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS3'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 2 doses on or after 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  1,
		  1970,
		  null,
		  'N',
		  '>=',
		  '2',
		  'Requires at least 2 doses on or before 12 months.'
		);",
		"insert into imm_rulesets_rules (ruleset_id, rule_id, rule_order, start_dt, end_dt, grade_limiter, operand, score, error_msg) values (
		  (select id from imm_rulesets where title = 'Florida:PNC:RS4'),
		  (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)'),
		  2,
		  1970,
		  null,
		  'N',
		  null,
		  null,
		  'Requires 1 dose on or after 12 months.'
		);",

		// 12. Default Loadout/imm_rulesets_rules_dynamic_values.sql
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '5'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and current_date + days_interval >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 4),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and current_date + days_interval >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 4),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '7 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''07'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''Tdap'', ''Td'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '5 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''08'', ''09'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''Tdap'', ''Td'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '10 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Measles:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Mumps:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Rubella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) and cast(shot_date + days_interval as date) <= cast('':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '12/31/2008'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv3:'' as date) and cast((dob + days_interval) + interval '':dv4:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv3:'' as date) and cast((dob + days_interval) + interval '':dv4:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Recombivax'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv3:'' as date) and cast((dob + days_interval) + interval '':dv4:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '11 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep B:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv3:'' as date) and cast((dob + days_interval) + interval '':dv4:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '15 years'),
		  4
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Meningococcal'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Meningococcal'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''07'', ''08'', ''09'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Meningococcal'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Meningococcal:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '10 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep A'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep A'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep A'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep A'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:Hep A:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''09'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '5 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '15 months'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '5 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '2 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Texas:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '5'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'B'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '7 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Q'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS5') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'P'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''07'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''Q'', ''P'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS6') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '5 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''08'', ''09'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'DTaP'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''Q'', ''P'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:DTaP:RS7') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code in (:dv2:) and current_date + days_interval <= cast(shot_date + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '10 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''KG'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '4'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 3),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '5'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Polio'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Polio:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '7 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Measles'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Measles:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Mumps'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Mumps:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '2'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Rubella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Rubella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '4 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '5 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '15 months'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'HIB'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:HIB:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv2:'' as date) and cast((dob + days_interval) + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B2'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv2:'' as date) and cast((dob + days_interval) + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '11 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date as date) between cast((dob - days_interval) + interval '':dv2:'' as date) and cast((dob + days_interval) + interval '':dv3:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '15 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B2'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B2:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B3:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Hep B3'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Hep B3:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'int' and dv_value = '3'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''PK'', ''10'', ''11'', ''12'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and gradelevel in (:dv2:) and shot_date is not null then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''KG'', ''01'', ''02'', ''03'', ''04'', ''05'', ''06'', ''07'', ''08'', ''09'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'Varicella'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:Varicella:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and state_code = '':dv2:'' then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'L'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '''EE'', ''PK'''),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS1') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and (gradelevel not in (:dv2:) or current_date + days_interval >= cast(dob + interval '':dv3:'' as date)) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '5 years'),
		  3
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS2') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '2 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS3') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) <= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 1),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = 'PNC'),
		  1
		);",
		"insert into imm_rulesets_rules_dynamic_values (rulesets_rules_id, dynamic_value_id, value_order) values (
		  (select id from imm_rulesets_rules where ruleset_id = (select id from imm_rulesets where title = 'Florida:PNC:RS4') and rule_id = (select id from imm_rules where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)') and rule_order = 2),
		  (select id from imm_dynamic_values where dv_type = 'chr' and dv_value = '1 years'),
		  2
		);",

		// 13. Functions/fn_imm_calc_data.sql
		"create or replace function fn_imm_calc_data (
		  p_student_id bigint default null,
		  p_syear int default null
		)
		returns table (
		  student_id bigint,
		  gradelevel varchar(5),
		  dob date,
		  days_interval int,
		  age int,
		  age_in_months int,
		  state_code varchar(10),
		  vaccine_class varchar(20),
		  vaccine_id int,
		  shot_date date,
		  vac_dose_num int,
		  class_dose_num int,
		  vac_lag_time_days int,
		  class_lag_time_days int
		)
		as $$
		declare
		  v_sql text;
		  v_shot_dates varchar;
		  v_selected_ruleset_group_id int;
		  v_student_id varchar;
		begin

		  if p_syear is null then
		    select cast(value as int) into p_syear from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null;
		  end if;

		  select code into v_shot_dates from imm_config where title = 'IMM_DATA_COLUMNS';

		  select id into v_selected_ruleset_group_id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = p_syear and school_id is null);

		  if p_student_id is null then
		    v_student_id := 'null';
		  else
		    v_student_id := p_student_id;
		  end if;

		  v_sql := concat('with vars as (
		  	select
		  		(select ', p_syear, ') syear,
		      (select ', v_selected_ruleset_group_id, ') vruleset_group_id
		  ),
		  student_info as (
		  	select
		  		se.student_id,
		  		se.gradelevel,
		  		cast(s.custom_200000004 as date) dob,
		  		date_part(''year'', age(s.custom_200000004)) age
		  	from (
		  		select
		  			se.end_date,
		  			se.student_id,
		  			sg.short_name gradelevel,
		  			row_number() over (partition by se.student_id order by se.end_date desc) rn
		  		from student_enrollment se
		  		join school_gradelevels sg
		  			on sg.id = se.grade_id
		  		where se.syear = (select syear from vars)
		  			and (se.student_id = ', v_student_id, ' or coalesce(', v_student_id, ', 1) = 1)
		  		group by se.end_date, se.student_id, sg.short_name) se
		  	join students s
		  		on s.student_id = se.student_id
		  	where se.rn = 1
		  ),
		  cjoin_data as (
		  	select
		  		si.student_id,
		  		si.gradelevel,
		  		si.dob,
		  		si.age,
		  		i.id,
		  		i.code,
		  		i.title as label
		  	from student_info si
		  	cross join imm_immunizations i
		    where i.id in (
		      select distinct
		        immunization_id
		      from imm_ruleset_groups_rulesets
		      where ruleset_group_id = (select vruleset_group_id from vars)
		        and (select syear from vars) >= start_dt
		        and ((select syear from vars) <= end_dt or end_dt is null)
		    )
		  ),
		  shot_info as (
		  	select distinct
		  		x.cfso_id,
		  		x.student_id,
		  		x.title,
		  		x.code,
		  		x.vaccine_id,
		  		rank() over (partition by x.student_id, x.title, x.vaccine_id order by cast(x.shot_dates as date)) vac_dose_num,
		  		rank() over (partition by x.student_id, x.title order by cast(x.shot_dates as date)) class_dose_num,
		  		cast(x.shot_dates as date) shot_date
		  	from (
		  	select
		  		cfle.id cfle_id,
		  		cfso.id cfso_id,
		  		cfle.source_id student_id,
		  		cfso.code,
		  		i.title,
		  		cfso.id vaccine_id,
					unnest(array_remove(array[', v_shot_dates, '], null)) shot_dates
		  	from custom_field_log_entries cfle
		  	join custom_field_select_options cfso
		  		on cast(cfso.id as varchar) = cfle.log_field1
		  	join imm_vaccines iv
		  		on lower(iv.code) = lower(cfso.code)
		  	join imm_immunizations i
		  		on i.id = iv.immunization_id
		  	where cfle.source_class = ''SISStudent''
		  		and cfle.field_id = (select cast(code as int) from imm_config where title = ''IMM_SHOTS_FIELD_ID'')
		  		and (cfle.source_id = ', v_student_id, ' or coalesce(', v_student_id, ', 1) = 1)
		      and iv.ruleset_group_id = (select vruleset_group_id from vars)
		  	) x
		    join students s
		      on s.student_id = x.student_id
		    where is_date(x.shot_dates) = true
		      and cast(x.shot_dates as date) >= cast(s.custom_200000004 as date)
		  )
		  select
		  	cast(cd.student_id as bigint) student_id,
		  	cd.gradelevel,
		  	cd.dob,
		  	(select cast(value as int) from program_config where program = ''system'' and title = ''IMM_INTERVAL_DAYS'' and syear = (select syear from vars) and school_id is null) days_interval,
		  	cast(cd.age as integer) age,
		  	cast(floor(cast(abs(cd.dob - si.shot_date) as float) / cast(30.27 as float)) as integer) age_in_months,
		  	si.code state_code,
		  	i.title vaccine_class,
		  	cast(si.cfso_id as integer) vaccine_id,
		  	si.shot_date,
		  	cast(si.vac_dose_num as integer) vac_dose_num,
		  	cast(si.class_dose_num as integer) class_dose_num,
		  	case when si.vac_dose_num = 1 then cast(abs(cd.dob - si.shot_date) as integer)
		  		else cast(abs((lag(si.shot_date, 1) over (partition by si.student_id, i.title, si.vaccine_id order by si.vac_dose_num)) - si.shot_date) as integer)
		  	end vac_lag_time_days,
		  	case when si.class_dose_num = 1 then cast(abs(cd.dob - si.shot_date) as integer)
		  		else cast(abs((lag(si.shot_date, 1) over (partition by si.student_id, i.title order by si.class_dose_num)) - si.shot_date) as integer)
		  	end class_lag_time_days
		  from cjoin_data cd
		  join imm_immunizations i
		  	on lower(i.code) = lower(cd.code)
		  left join shot_info si
		  	on si.student_id = cd.student_id and si.title = i.title
		  where si.shot_date >= cd.dob or si.shot_date is null');

		  return query execute v_sql;

		end;
		$$
		language plpgsql;",

		// 14. Functions/fn_imm_get_rulesets.sql
		"create or replace function fn_imm_get_rulesets (
		  p_ruleset_group varchar,
		  p_immunization varchar,
		  p_syear int
		)
		returns table (
		  title varchar,
		  t1error varchar,
		  t2error varchar,
			t3error varchar,
			t1comp varchar,
			t2comp varchar,
			code text,
			grade_limiter varchar,
			operand varchar,
			score varchar,
		  rule_order int
		)
		as $$
		begin

			return query
			with vars as (
				select
					(select id from imm_ruleset_groups rg where rg.title = p_ruleset_group) ruleset_group_id,
					(select id from imm_immunizations i where i.code = p_immunization) immunization_id
			),
			dv_vars as (
			select
				rr.id,
				rrdv.value_order,
				dv.dv_value
			from imm_rulesets_rules rr
			join imm_rulesets rs
			  on rs.id = rr.ruleset_id
			join imm_ruleset_groups_rulesets rgr
			  on rgr.ruleset_id = rs.id
			join imm_rulesets_rules_dynamic_values rrdv
			  on rrdv.rulesets_rules_id = rr.id
			join imm_dynamic_values dv
			  on dv.id = rrdv.dynamic_value_id
			where rgr.ruleset_group_id = (select ruleset_group_id from vars)
				and rgr.immunization_id = (select immunization_id from vars)
		    and (p_syear between rgr.start_dt and rgr.end_dt or (rgr.end_dt is null and rgr.start_dt <= p_syear))
		    and (p_syear between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= p_syear))
			),
			dynamic_rules as (
			select
				rr.id,
				case (select max(value_order) from dv_vars where id = rr.id)
				when 5 then
					replace(replace(replace(replace(
					replace(r.code, (select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 2), (select dv_value from dv_vars where id = rr.id and value_order = 2)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 3), (select dv_value from dv_vars where id = rr.id and value_order = 3)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 4), (select dv_value from dv_vars where id = rr.id and value_order = 4)),
		      (select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 5), (select dv_value from dv_vars where id = rr.id and value_order = 5))
				when 4 then
					replace(replace(replace(
					replace(r.code, (select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 2), (select dv_value from dv_vars where id = rr.id and value_order = 2)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 3), (select dv_value from dv_vars where id = rr.id and value_order = 3)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 4), (select dv_value from dv_vars where id = rr.id and value_order = 4))
				when 3 then
					replace(replace(
					replace(r.code, (select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 2), (select dv_value from dv_vars where id = rr.id and value_order = 2)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 3), (select dv_value from dv_vars where id = rr.id and value_order = 3))
				when 2 then
					replace(
					replace(r.code, (select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1)),
					(select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 2), (select dv_value from dv_vars where id = rr.id and value_order = 2))
				when 1 then
					replace(r.code, (select concat(':dv', value_order, ':') from dv_vars where id = rr.id and value_order = 1), (select dv_value from dv_vars where id = rr.id and value_order = 1))
				end code
			from imm_rules r
			join imm_rulesets_rules rr
				on rr.rule_id = r.id
		  where (p_syear between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= p_syear))
			)
			select
				rs.title title,
				rgr.error_msg t1error,
				coalesce(rs.error_msg, rs.compliance_msg) t2error,
				rr.error_msg t3error,
				rgr.compliance_msg t1comp,
				rs.compliance_msg t2comp,
				dr.code code,
				rr.grade_limiter,
				rr.operand,
				rr.score,
		    rr.rule_order
			from imm_ruleset_groups_rulesets rgr
			join imm_rulesets rs
				on rs.id = rgr.ruleset_id
			join imm_rulesets_rules rr
				on rr.ruleset_id = rs.id
			join dynamic_rules dr
				on dr.id = rr.id
			where rgr.ruleset_group_id = (select ruleset_group_id from vars)
				and rgr.immunization_id = (select immunization_id from vars)
		    and (p_syear between rgr.start_dt and rgr.end_dt or (rgr.end_dt is null and rgr.start_dt <= p_syear))
		    and (p_syear between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= p_syear))
			order by rgr.imm_order, rs.title, rr.rule_order;
		end;
		$$
		language plpgsql;",

		// 15. Functions/fn_imm_calc.sql
		"create or replace function fn_imm_calc(
		  p_immunization varchar,
		  p_student_id bigint default null,
		  p_syear int default null,
		  p_debug int default 0
		)
		  returns text
		as $$

		declare
		  v_imm_id int;
		  v_imm_col_id int;
		  v_selected_ruleset_group_id int;
		  v_offset_days int;
		  v_error_handling varchar;
		  v_added_filter varchar := chr(32);
		  v_sql text;
		  v_rs_counter int;
		  v_rs_selector int;
		  cr record;
		begin

		  -- Select current syear to calculate against.
		  if p_syear is null then
		    select cast(value as int) into p_syear from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null;
		  end if;

		  -- Catch to ensure the Dynamic Version for Immunizations system has all required variables set.
		  if (select
		      count(*)
		    from program_config
		    where title in (
		        'IMM_COMPLIANCE',
		        'IMM_SELECTED_RULESET_GROUP',
		        'IMM_ERROR_HANDLING',
		        'IMM_INTERVAL_DAYS'
		      )
		      and syear = p_syear
		      and program = 'system'
		      and school_id is null
		      and value is null) > 0
		  then
		    return null;
		  end if;

		  -- Check imm_config as well.
		  if (select
		      count(*)
		    from imm_config
		    where code is null) > 0
		  then
		    return null;
		  end if;

		  -- Ensure Immunization Module is active.
		  if (select value from program_config where program = 'system' and title = 'IMM_COMPLIANCE' and syear = p_syear and school_id is null) <> 'Y' then
		    if p_debug <> 1 then
		      return null;
		    else
		      -- Return message to turn on Immunization Compliance.
		      return 'Requires Immunization Compliance to be enabled within System Preferences.';
		    end if;
		  end if;

		  -- Ensure an immunization was selected to run against.
		  if p_immunization is null then
		    return null;
		  else
		    -- Pull the immunization_id.
		    select id into v_imm_id from imm_immunizations where code = p_immunization;
		  end if;

		  -- Ensure an immunization was pulled.
		  if v_imm_id is null then
		    return null;
		  end if;

		  -- Pull the column id for the custom_field to update.
		  select id into v_imm_col_id from custom_fields where column_name = (select code from imm_config where title = 'IMM_CUSTOM_COL_NAME');

		  -- Pull the selected_ruleset_group from config table.
		  select id into v_selected_ruleset_group_id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = p_syear and school_id is null);

		  -- Pull the offset_days.
		  select cast(value as int) into v_offset_days from program_config where program = 'system' and title = 'IMM_INTERVAL_DAYS' and syear = p_syear and school_id is null;

		  -- Pull error handling method.
		  select value into v_error_handling from program_config where program = 'system' and title = 'IMM_ERROR_HANDLING' and syear = p_syear and school_id is null;

		  -- Ensure an immunization was selected to run against.
		  if p_immunization is null then
		    return null;
		  else
		    -- Pull the immunization_id.
		    select
		      i.id into v_imm_id
		    from imm_immunizations i
		    join imm_vaccines v
		      on v.immunization_id = i.id
		    where i.code = p_immunization
		      and v.ruleset_group_id = v_selected_ruleset_group_id;
		    -- Test to make sure this immunization exists in the current selected ruleset group.
		    if v_imm_id is null then
		      return null;
		    end if;
		  end if;

		  -- Singleton student override query value.
		  if p_student_id is not null then
		    v_added_filter := concat(' and cfle.source_id = ', p_student_id);
		  end if;

		  -- Generate v_sql.
		  v_sql := '
		  with t1 as (
		    select
		      student_id,
		      vaccine_class,
		  ';

		  for cr in
		    select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		  loop
		    v_sql := concat(v_sql, cr.code, ' ', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ', ');
		  end loop;

		  if p_student_id is null then
		    v_sql := concat(substring(v_sql for length(v_sql) - 2), ' from fn_imm_calc_data(null, ', p_syear, ')');
		  else
		    v_sql := concat(substring(v_sql for length(v_sql) - 2), ' from fn_imm_calc_data(', p_student_id, ', ', p_syear, ')');
		  end if;

		  v_sql := concat(v_sql, ' group by 1, 2),
		  t2 as (
		    select
		      student_id,
		  ');

		  for cr in
		    select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		  loop
		    if cr.score is null then
		      v_sql := concat(v_sql, 'case when max(', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ') >= 1 then 1 else 0 end ', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order, ', ');
		    else
		      v_sql := concat(v_sql, 'case when max(', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ') ', cr.operand, ' ', cr.score, ' then 1 else 0 end ', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order, ', ');
		    end if;
		  end loop;

		  v_sql := concat(substring(v_sql for length(v_sql) - 2), ' from t1 group by 1),
		  t3 as (
		    select
		      student_id,
		      (select title from imm_immunizations where id = ', v_imm_id, ') vaccine_class,
		      cast(cast((');

		  -- Reset counter.
		  v_rs_counter := 1;
		  v_rs_selector := 1;

		  for cr in
		    select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		  loop
		    if v_rs_selector <> cast(substring(cr.title from length(cr.title)) as int) then
		      v_sql := concat(substring(v_sql for length(v_sql) - 3), ') as float)/cast(', v_rs_counter,' as float)*100 as int) rs', v_rs_selector, ', cast(cast((');
		      v_sql := concat(v_sql, substring(cr.title from length(cr.title) - 2), '_', cr.rule_order, ' + ');
		      v_rs_selector := cast(substring(cr.title from length(cr.title)) as int);
		      v_rs_counter := cr.rule_order;
		    else
		      v_sql := concat(v_sql, ' ', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order, ' + ');
		      v_rs_counter := cr.rule_order;
		    end if;
		  end loop;

		  v_sql := concat(substring(v_sql for length(v_sql) - 3), ') as float)/cast(', v_rs_counter,' as float)*100 as int) rs', v_rs_selector, ' from t2),');

		  v_sql := concat(v_sql, '
		  t3pe as (
		    select
		      student_id,
		      unnest(array[');

		  for cr in
		    select distinct
		      rs.title,
		      concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
		      rs.t2error,
		      gl.code
		    from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
		    left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
		      on rs.title = gl.title and gl.grade_limiter = 'Y'
		    order by rs.title
		  loop
		    v_sql := concat(v_sql, '''', cr.rs , ''', ');
		  end loop;

		  v_sql := concat(substring(v_sql for length(v_sql) - 2), ']) rs,
		    unnest(array[');

		  for cr in
		    select distinct
		      rs.title,
		      concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
		      rs.t2error,
		      gl.code
		    from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
		    left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
		      on rs.title = gl.title and gl.grade_limiter = 'Y'
		    order by rs.title
		  loop
		    v_sql := concat(v_sql, cr.rs , ', ');
		  end loop;

		  v_sql := concat(substring(v_sql for length(v_sql) - 2), ']) score,
		    unnest(array[');

		  for cr in
		    select distinct
		      rs.title,
		      concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
		      rs.t2error,
		      gl.code
		    from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
		    left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
		      on rs.title = gl.title and gl.grade_limiter = 'Y'
		    order by rs.title
		  loop
		    v_sql := concat(v_sql, '''', cr.t2error , ''', ');
		  end loop;

		  v_sql := concat(substring(v_sql for length(v_sql) - 2), ']) error_msg,
		    unnest(array[');

		  for cr in
		    select distinct
		      rs.title,
		      concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
		      rs.t2error,
		      gl.code
		    from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
		    left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
		      on rs.title = gl.title and gl.grade_limiter = 'Y'
		    order by rs.title
		  loop
		    if cr.code is not null then
		      if p_student_id is null then
		        v_sql := concat(v_sql, '(select ', cr.code , ' from fn_imm_calc_data(null, ', p_syear, ') where vaccine_class = (select title from imm_immunizations where id = ', v_imm_id, ')), ');
		      else
		        v_sql := concat(v_sql, '(select ', cr.code , ' from fn_imm_calc_data(', p_student_id, ', ', p_syear, ') where vaccine_class = (select title from imm_immunizations where id = ', v_imm_id, ')), ');
		      end if;
		    else
		      v_sql := concat(v_sql, ' 0, ');
		    end if;
		  end loop;

		  v_sql := concat(substring(v_sql for length(v_sql) - 2),']) gl_test,
		    unnest(array[');

		  for cr in
		    select distinct
		      rs.title,
		      concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
		      rs.t2error,
		      gl.code
		    from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
		    left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
		      on rs.title = gl.title and gl.grade_limiter = 'Y'
		    order by rs.title
		  loop
		    if cr.code is not null then
		      v_sql := concat(v_sql, ' 1, ');
		    else
		      v_sql := concat(v_sql, ' 0, ');
		    end if;
		  end loop;

		  v_sql := concat(substring(v_sql for length(v_sql) - 2),']) g_test from t3),
		    t3e as (select *, rank() over (partition by student_id order by gl_test desc, score desc, rs) from t3pe where (g_test = 1 and gl_test > 0) or g_test = 0),
		  ');

		  if v_error_handling = 'Itemized' then

		    v_sql := concat(v_sql,
		    ' eitems as (
		    	select
		    		concat(substring(r.title from length(r.title) - 2), ''_'', rr.rule_order) rs,
		    		rr.error_msg,
		        rgr.error_msg imm_error_msg
		    	from imm_rulesets r
		    	join imm_rulesets_rules rr
		    		on rr.ruleset_id = r.id
		      join imm_ruleset_groups_rulesets rgr
		        on rgr.ruleset_id = r.id
		      where rgr.immunization_id = ', v_imm_id, '
		        and (', p_syear, ' between rgr.start_dt and rgr.end_dt or (rgr.end_dt is null and rgr.start_dt <= ', p_syear, '))
		        and (', p_syear, ' between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= ', p_syear, '))
		    	order by r.title, rr.rule_order
		    ),
		    escores as (
		    	select
		    		student_id,
		        unnest(array[');

		    for cr in
		      select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		    loop
		      v_sql := concat(v_sql, '''', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ''', ');
		    end loop;

		    v_sql := concat(substring(v_sql for length(v_sql) - 2),']) rs,
		    unnest(array[');

		    for cr in
		      select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		    loop
		      v_sql := concat(v_sql, substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ', ');
		    end loop;

		    v_sql := concat(substring(v_sql for length(v_sql) - 2),']) score
		    	from t2
		    ),
		    err_item as (
		      select
		        es.student_id,
		        ei.imm_error_msg,
		        string_agg(ei.error_msg, '' '' order by es.rs) error_msg
		      from escores es
		      join eitems ei
		        on ei.rs = es.rs
		      join t3e
		        on t3e.rs = substring(lower(es.rs) for 3) and t3e.rank = 1
		      where es.score = 0
		      group by es.student_id, ei.imm_error_msg
		    ),');

		  end if;

		  v_sql := concat(v_sql, '
		  vars as (
		    select ', v_imm_col_id, ' cfid
		  )
		  , imm as (
		    select distinct
		      i.id,
		      i.code
		    from imm_immunizations i
		    join imm_ruleset_groups_rulesets rgr
		      on rgr.immunization_id = i.id
		    join imm_ruleset_groups rg
		      on rg.id = rgr.ruleset_group_id
		    where rg.title = (select value from program_config where title = ''IMM_SELECTED_RULESET_GROUP'')
		      and ', p_syear ,' >= start_dt
		      and (', p_syear ,' <= end_dt or end_dt is null)
		  )
		  , exempt as (
				select distinct
					cfle.source_id as student_id,
					i.title as vaccine_class,
					cfso.label as exemption
			  from custom_field_log_entries cfle
		    join custom_field_select_options cfso
		      on cast(cfso.id as varchar) = cfle.log_field2
				join imm_immunizations i
		      on cast(i.id as varchar) = cfle.log_field1
		    join imm_ruleset_groups_rulesets rgr
		      on rgr.immunization_id = i.id
		    join imm_ruleset_groups rg
		      on rg.id = rgr.ruleset_group_id
		    where rg.title = (select value from program_config where title = ''IMM_SELECTED_RULESET_GROUP'')
		      and ', p_syear ,' >= start_dt
		      and (', p_syear ,' <= end_dt or end_dt is null)
		  		and cfle.field_id = (select cast(code as int) from imm_config where title = ''IMM_EXEMPT_FIELD_ID'') ',
				  v_added_filter
				  , ' and current_date between cast(cfle.log_field3 as date)
		      and coalesce(cast(cfle.log_field4 as date), current_date)
		  ),
		  data as (
		    select distinct
		      t3.student_id,
		      (select id from imm where lower(code) = ''', p_immunization, ''') as vaccine_id,
		      case when e.vaccine_class = ''', (select title from imm_immunizations where id = v_imm_id), ''' and e.exemption is not null then ''Yes''
		  ');

		  -- Reset counter.
		  v_rs_counter := 0;

		  for cr in
		    select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		  loop
		    if v_rs_counter <> cast(substring(cr.title from length(cr.title)) as int) then
		      v_rs_counter := cast(substring(cr.title from length(cr.title)) as int);
		      v_sql := concat(v_sql, ' when t3.rs', v_rs_counter, ' = 100 then ''Yes'' ');
		    end if;
		  end loop;

		  v_sql := concat(v_sql, ' else ''No'' end as compliant,
		  case when e.vaccine_class = t3.vaccine_class and e.exemption is not null then ''Student is exempt.'' ');

		  -- Reset counter.
		  v_rs_counter := 0;

		  for cr in
		    select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		  loop
		    if v_rs_counter <> cast(substring(cr.title from length(cr.title)) as int) then
		      v_rs_counter := cast(substring(cr.title from length(cr.title)) as int);
		      if v_error_handling = 'Generic' then
		        v_sql := concat(v_sql, ' when t3.rs', v_rs_counter, ' = 100 then ''', cr.t1comp, ''' ');
		      else
		        v_sql := concat(v_sql, ' when t3.rs', v_rs_counter, ' = 100 then ''', cr.t2comp, ''' ');
		      end if;
		    end if;
		  end loop;

		  if v_error_handling = 'Generic' then
		    v_sql := concat(v_sql, ' else ''', cr.t1error, ''' end as required_doses,');
		  elseif v_error_handling = 'Ruleset' then
		    v_sql := concat(v_sql, ' else t3e.error_msg end as required_doses,');
		  elseif v_error_handling = 'Itemized' then
		    v_sql := concat(v_sql, ' else concat(ei.imm_error_msg, '' '', ei.error_msg) end as required_doses,');
		  else
		    v_sql := concat(v_sql, ' else null end as required_doses,');
		  end if;

		  v_sql := concat(v_sql, ' case when e.exemption is null then ''No exemption'' else e.exemption end as exemption_msg,
		  (select cfid from vars) as custom_field_id
		  from t3 ');

		  if v_error_handling <> 'Generic' then
		    v_sql := concat(v_sql, ' join t3e
		      on t3e.student_id = t3.student_id and t3e.rank = 1');
		  end if;

		  if v_error_handling = 'Itemized' then
		    v_sql := concat(v_sql, ' left join err_item ei
		      on ei.student_id = t3.student_id');
		  end if;

		  v_sql := concat(v_sql, ' left join exempt e
		    on e.student_id = t3.student_id and e.vaccine_class = t3.vaccine_class),
		  cte_update as (
			  update custom_field_log_entries cfle set
				  log_field2 = d.compliant,
				  log_field3 = d.required_doses,
				  log_field4 = d.exemption_msg,
				  log_field5 = ''u''
			  from data as d
			  join custom_field_log_entries le on d.student_id = le.source_id
				  and d.custom_field_id = le.field_id
				  and cast(d.vaccine_id as varchar) = le.log_field1
			  where cfle.id = le.id
			    and (cfle.log_field2 <> d.compliant
			      or cfle.log_field3 <> d.required_doses
			      or cfle.log_field4 <> d.exemption_msg)

			  returning d.*
		  )
		  insert into custom_field_log_entries (
		    id,
		 	  source_class,
			  source_id,
			  field_id,
			  log_field1,
			  log_field2,
			  log_field3,
			  log_field4,
			  log_field5
		  )
		  select
		    nextval(''custom_field_log_entries_seq''),
		    ''SISStudent'',
		    d.student_id,
		    d.custom_field_id,
		    d.vaccine_id,
		    d.compliant,
		    d.required_doses,
		    d.exemption_msg,
		    ''i''
		  from data as d
		  left join cte_update as u
		    on u.student_id = d.student_id
			    and u.custom_field_id = d.custom_field_id
			    and u.vaccine_id = d.vaccine_id
		  where not exists (select ''''
			  from custom_field_log_entries c
				where c.source_id = d.student_id
				  and c.field_id = d.custom_field_id
					and c.log_field1 = cast(d.vaccine_id as varchar)
			);');

		  if p_debug <> 1 then
		    -- Run the query.
		    execute v_sql;
		  else
		    -- Return the query.
		    return v_sql;
		  end if;

		  -- Exception to remove Florda Hep B2 or Hep B3 No's when the other is Yes.
		  if p_immunization in ('hepb2', 'hepb3') then
		    for cr in
		      with rgr as (
		        select distinct
		          rgr.immunization_id
		        from imm_ruleset_groups_rulesets rgr
		        where rgr.ruleset_group_id = v_selected_ruleset_group_id
		          and p_syear >= rgr.start_dt
		          and (p_syear <= rgr.end_dt or rgr.end_dt is null)
		      ),
		      ret_recs as (
		        select
		          cfle.source_id,
		          max(case when cfle.log_field2 = 'No' then cfle.id else null end) cfle_id,
		          sum(case when cfle.log_field2 = 'Yes' then 1 else 0 end) as has_yes
		        from imm_immunizations i
		        join rgr
		          on rgr.immunization_id = i.id
		        join custom_field_log_entries cfle
		          on cfle.log_field1 = cast(i.id as varchar)
		        where i.code in ('hepb2', 'hepb3')
		          and (cfle.source_id = p_student_id or coalesce(p_student_id, 1) = 1)
		        group by cfle.source_id
		      )
		      select
		        cfle_id,
		        source_id
		      from ret_recs
		      where cfle_id is not null
		        and has_yes > 0
		    loop
		      delete from custom_field_log_entries where id = cr.cfle_id and source_id = cr.source_id and source_class = 'SISStudent' and log_field2 = 'No';
		    end loop;
		  end if;
		  return null;

		end;
		$$ language plpgsql;",

		// 16. Functions/fn_imm_calc_clean.sql
		"create or replace function fn_imm_calc_clean(
		  p_student_id bigint default null,
		  p_syear int default null
		)
		  returns text
		as $$

		declare
			v_imm_col_id int;
			v_selected_ruleset_group_id int;
		  cr record;
		begin

			-- Select current syear to calculate against.
			if p_syear is null then
				select cast(value as int) into p_syear from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null;
			end if;

		  -- Catch to ensure the Dynamic Version for Immunizations system has all required variables set.
		  if (select
		      count(*)
		    from program_config
		    where title in (
		        'IMM_COMPLIANCE',
		        'IMM_SELECTED_RULESET_GROUP',
		        'IMM_ERROR_HANDLING',
		        'IMM_INTERVAL_DAYS'
		      )
		      and syear = p_syear
		      and program = 'system'
		      and school_id is null
		      and value is null) > 0
		  then
		    return null;
		  end if;

		  -- Check imm_config as well.
		  if (select
		      count(*)
		    from imm_config
		    where code is null) > 0
		  then
		    return null;
		  end if;

		  -- Ensure Immunization Module is active.
		  if (select value from program_config where program = 'system' and title = 'IMM_COMPLIANCE' and syear = p_syear and school_id is null) <> 'Y' then
		    return null;
		  end if;

			-- Pull the column id for the custom_field to update.
			select id into v_imm_col_id from custom_fields where column_name = (select code from imm_config where title = 'IMM_CUSTOM_COL_NAME');

			-- Pull the selected_ruleset_group from config table.
			select id into v_selected_ruleset_group_id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = p_syear and school_id is null);

			if v_imm_col_id is null or v_selected_ruleset_group_id is null then
				return null;
			end if;

			if p_student_id is null then
				for cr in
					select
						cfle.id,
						cfle.source_id,
						cfle.log_field1,
						i.code,
						rg.immunization_id
					from custom_field_log_entries cfle
					join imm_immunizations i
						on cast(i.id as varchar) = cfle.log_field1
					left join (
						select distinct
		          rgr.immunization_id
		        from imm_ruleset_groups_rulesets rgr
		        where rgr.ruleset_group_id = v_selected_ruleset_group_id
		          and p_syear >= rgr.start_dt
		          and (p_syear <= rgr.end_dt or rgr.end_dt is null)) rg
						on rg.immunization_id = i.id
					where cfle.field_id = v_imm_col_id
						and rg.immunization_id is null
				loop
					delete from custom_field_log_entries where id = cr.id and source_id = cr.source_id and source_class = 'SISStudent' and field_id = v_imm_col_id;
				end loop;
			else
				for cr in
					select
						cfle.id,
						cfle.source_id,
						cfle.log_field1,
						i.code,
						rg.immunization_id
					from custom_field_log_entries cfle
					join imm_immunizations i
						on cast(i.id as varchar) = cfle.log_field1
					left join (
						select distinct
		          rgr.immunization_id
		        from imm_ruleset_groups_rulesets rgr
		        where rgr.ruleset_group_id = v_selected_ruleset_group_id
		          and p_syear >= rgr.start_dt
		          and (p_syear <= rgr.end_dt or rgr.end_dt is null)) rg
						on rg.immunization_id = i.id
					where cfle.field_id = v_imm_col_id
						and cfle.source_id = p_student_id
						and rg.immunization_id is null
				loop
					delete from custom_field_log_entries where id = cr.id and source_id = cr.source_id and source_class = 'SISStudent' and field_id = v_imm_col_id;
				end loop;
			end if;

			return null;

		end;
		$$ language plpgsql;",

		// 17. Functions/fn_imm_uninstall.sql
		"create or replace function fn_imm_uninstall ()
			returns text
		as $$
		begin

			drop function fn_imm_calc_clean(bigint, int);
			drop function fn_imm_calc(varchar, bigint, int, int);
			drop function fn_imm_calc_data(bigint, int);
			drop function fn_imm_get_rulesets(varchar, varchar, int);

			drop table imm_ruleset_groups_rulesets;
			drop table imm_vaccines;
			drop table imm_rulesets_rules_dynamic_values;
			drop table imm_rulesets_rules;
			drop table imm_rulesets;
			drop table imm_ruleset_groups;
			drop table imm_rules;
			drop table imm_immunizations;
			drop table imm_dynamic_values;
			drop table imm_config;

			delete from program_config where lower(title) like 'imm_%';
			delete from custom_fields_join_categories where field_id in (select id from custom_fields where alias in ('immunization_compliance', 'immunization_exemption'));
			delete from permission where key like concat('SISStudent:', (select id from custom_fields where alias = 'immunization_compliance'), '%') and (select count(id) from custom_fields where alias = 'immunization_compliance') = 1;
			delete from permission where key like concat('SISStudent:', (select id from custom_fields where alias = 'immunization_exemption'), '%') and (select count(id) from custom_fields where alias = 'immunization_exemption') = 1;
			delete from edit_rule_criteria where rule_id in (select id from edit_rules where lower(name) like 'imm -%');
			delete from edit_rules where lower(name) like 'imm -%';
			delete from custom_field_log_entries where field_id in (select id from custom_fields where alias in ('immunization_compliance', 'immunization_exemption'));
			delete from custom_field_select_options where source_class = 'CustomFieldLogColumn' and source_id in (select id from custom_field_log_columns where type = 'select' and field_id in (select id from custom_fields where alias in ('immunization_compliance', 'immunization_exemption')));
			delete from custom_field_log_columns where field_id in (select id from custom_fields where alias in ('immunization_compliance', 'immunization_exemption'));
			delete from custom_fields where alias in ('immunization_compliance', 'immunization_exemption');
			delete from custom_reports where title = 'Compliance Report' and parent_id = (select id from custom_reports_folders where title = 'Immunizations');
			delete from custom_reports_folders where title = 'Immunizations';
			delete from custom_reports_variables where variable_name like 'IMM_%';

			delete from cron_jobs where class = 'ImmunizationsNightlyJob';

			update database_migrations set deleted = '1' where migration_id = 'FOCUS-13030' and deleted is null;

			return 'Uninstall completed, please drop this function now.';

		end;
		$$
		language plpgsql;",

		// 18. ../../../../2. Database Objects/PostgreSQL/Functions/is_date.sql
		"create or replace function is_date(p_date varchar)
		returns boolean
		as $$
		begin
		  perform p_date::date;
		  return true;
		exception when others then
		  return false;
		end;
		$$ language plpgsql;"
  ];

}

// Execute sql array.
foreach ($sql as $sqlLine) {
	Database::query($sqlLine);
}

// Report Logic.
$rcounter = Database::get("select count(*) as c from custom_reports_folders where title = 'Immunizations' and parent_id = '#' and package = 'SIS';");
$rcounter = $rcounter[0]['C'];
if ($rcounter == 0) {
	Database::query("insert into custom_reports_folders (id, title, parent_id, package) values ((select coalesce(max(cast(id as int)), 0) + 1 from custom_reports_folders), 'Immunizations', '#', 'SIS');");
}

$rcounter = Database::get("select count(*) as c from custom_reports_variables where variable_name = '{IMM_DIST_SCHOOL}';");
$rcounter = $rcounter[0]['C'];
if ($rcounter == 0) {
	if (Database::$type === 'mssql') {
		Database::query("insert into custom_reports_variables (id, variable_name, variable_type, default_value, pulldown_options, interface_title, package) values ((select coalesce(max(cast(id as int)), 0) + 1 from custom_reports_variables), '{IMM_DIST_SCHOOL}', 1, 'No', concat('Yes [ ]', char(10), 'No [', char(45), char(45), ']'), 'School Specific?', 'SIS');");
	} else if (Database::$type === 'postgres') {
		Database::query("insert into custom_reports_variables (id, variable_name, variable_type, default_value, pulldown_options, interface_title, package) values ((select coalesce(max(cast(id as int)), 0) + 1 from custom_reports_variables), '{IMM_DIST_SCHOOL}', 1, 'No', concat('Yes [ ]', chr(10), 'No [', chr(45), chr(45), ']'), 'School Specific?', 'SIS');");
	}
}

$rcounter = Database::get("select count(*) as c from custom_reports_variables where variable_name = '{IMM_TYPE}';");
$rcounter = $rcounter[0]['C'];
if ($rcounter == 0) {
	if (Database::$type === 'mssql') {
		Database::query("insert into custom_reports_variables (id, variable_name, variable_type, default_value, pulldown_options, interface_title, package) values ((select coalesce(max(cast(id as int)), 0) + 1 from custom_reports_variables), '{IMM_TYPE}', 1, 'All',
			concat(
				'All [ ]', chr(10),
				'DTaP [and i.code = ''dtp'']', chr(10),
				'Hep B2 [and i.code = ''hepb2'']', chr(10),
				'Hep B3 [and i.code = ''hepb3'']', chr(10),
				'HIB [and i.code = ''hib'']', chr(10),
				'Measles [and i.code = ''ms'']', chr(10),
				'Mumps [and i.code = ''mu'']', chr(10),
				'PNC [and i.code = ''pnc'']', chr(10),
				'Polio [and i.code = ''pol'']', chr(10),
				'Rubella [and i.code = ''rub'']', chr(10),
				'Varicella [and i.code = ''var'']'),
			'Immunization Type?', 'SIS');");
	} else if (Database::$type === 'postgres') {
		Database::query("insert into custom_reports_variables (id, variable_name, variable_type, default_value, pulldown_options, interface_title, package) values ((select coalesce(max(cast(id as int)), 0) + 1 from custom_reports_variables), '{IMM_TYPE}', 1, 'All',
			concat(
				'All [ ]', chr(10),
				'DTaP [and i.code = ''dtp'']', chr(10),
				'Hep B2 [and i.code = ''hepb2'']', chr(10),
				'Hep B3 [and i.code = ''hepb3'']', chr(10),
				'HIB [and i.code = ''hib'']', chr(10),
				'Measles [and i.code = ''ms'']', chr(10),
				'Mumps [and i.code = ''mu'']', chr(10),
				'PNC [and i.code = ''pnc'']', chr(10),
				'Polio [and i.code = ''pol'']', chr(10),
				'Rubella [and i.code = ''rub'']', chr(10),
				'Varicella [and i.code = ''var'']'),
			'Immunization Type?', 'SIS');");
	}
}

$rcounter = Database::get("select count(*) as c from custom_reports where title = 'Compliance Report' and parent_id = (select id from custom_reports_folders where title = 'Immunizations' and parent_id = '#' and package = 'SIS');");
$rcounter = $rcounter[0]['C'];
if ($rcounter == 0) {
	if (Database::$type === 'mssql') {
		Database::query("insert into custom_reports (id, title, query, singular, plural, school_id, profiles, portal_alert, parent_id, multiple_queries, is_chart, package) values ((select coalesce(max(cast(id as int)), 0) + 1 from custom_reports), 'Compliance Report',
			'/* Immunization Compliance Report
			Author: Rob Noe
			Ticket:
				JIRA: 13030
				Zendesk: 115582
			Date: 10/25/2017
			Reason: Create a report to display district immunization compliance issues.
			Requires Variables: imm_type.sql, imm_dist_school.sql
			*/
			with vars as (
				select {SYEAR} as syear,
				{SCHOOL_ID} as school_id
			),
			imm as (
				select
					(select cast(id as int) from custom_fields where column_name = (select code from imm_config where title = ''IMM_CUSTOM_COL_NAME'')) comp_field_id,
					(select id from imm_ruleset_groups where title = (select value from program_config where syear = (select syear from vars) and school_id is null and program = ''system'' and title = ''IMM_SELECTED_RULESET_GROUP'')) ruleset_group_id
			),
			ruleset_group as (
				select distinct
					rgr.immunization_id
				from imm_ruleset_groups_rulesets rgr
				where rgr.ruleset_group_id = (select ruleset_group_id from imm)
			),
			comp_values as (
				select
					cfle.source_id student_id,
					cfle.log_field1 cfso_vaccine_id,
					cfle.log_field2 comp_value,
					cfle.log_field3 rule_value,
					cfle.log_field4 exempt_value
				from custom_field_log_entries cfle
				where cfle.field_id = (select comp_field_id from imm)
			)
			select
				cv.student_id as \"student id\",
				sc.custom_327 school,
				concat(s.last_name, '', '', s.first_name) as \"student name\",
				cast(s.custom_200000004 as date) dob,
				sg.short_name grade,
				imm_status.code status,
				cfso.label immunization,
				cv.rule_value as \"error\"
			from comp_values cv
			join custom_field_select_options cfso
				on cast(cfso.id as varchar) = cv.cfso_vaccine_id and cfso.source_class = ''CustomFieldLogColumn''
			join student_enrollment se
				on se.student_id = cv.student_id and se.syear = (select syear from vars)
			join students s
				on s.student_id = cv.student_id
			join schools sc
				on sc.id = se.school_id
			join school_gradelevels sg
				on sg.id = se.grade_id
			join custom_field_select_options imm_status
				on imm_status.id = s.custom_630
			join imm_immunizations i
				on i.code = lower(cfso.code)
			join ruleset_group rg
				on rg.immunization_id = i.id
			where cv.comp_value = ''No''
				and coalesce(se.custom_9, ''N'') != ''Y''
				and (se.end_date is null or getdate() between se.start_date and se.end_date)
				{IMM_DIST_SCHOOL} and se.school_id = (select school_id from vars)
				{IMM_TYPE}
			order by sc.custom_327, cast(replace(replace(sg.short_name, ''KG'', ''0.75''), ''PK'', ''0.5'') as float), concat(s.last_name, '', '', s.first_name), cfso.label;',
			'Record',
			'Records',
			'0',
			(select concat('||', min(id), '||') from user_profiles where type = 'super'),
			'N',
			(select id from custom_reports_folders where title = 'Immunizations' and parent_id = '#' and package = 'SIS'),
			'N',
			'N',
			'SIS');");
	} else if (Database::$type === 'postgres') {
		Database::query("insert into custom_reports (id, title, query, singular, plural, school_id, profiles, portal_alert, parent_id, multiple_queries, is_chart, package, description, execute_only) values ((select coalesce(max(cast(id as int)), 0) + 1 from custom_reports), 'Compliance Report',
			'/* Immunization Compliance Report
			Author: Rob Noe
			Ticket:
				JIRA: 13030
				Zendesk: 115582
			Date: 10/25/2017
			Reason: Create a report to display district immunization compliance issues.
			Requires Variables: imm_type.sql, imm_dist_school.sql
			*/
			with vars as (
				select {SYEAR} as syear,
				{SCHOOL_ID} as school_id
			),
			imm as (
				select
					(select cast(id as int) from custom_fields where column_name = (select code from imm_config where title = ''IMM_CUSTOM_COL_NAME'')) comp_field_id,
					(select id from imm_ruleset_groups where title = (select value from program_config where syear = (select syear from vars) and school_id is null and program = ''system'' and title = ''IMM_SELECTED_RULESET_GROUP'')) ruleset_group_id
			)
			select
				cfle.source_id as \"student id\",
				sc.custom_327 school,
				concat(s.last_name, '', '', s.first_name) as \"student name\",
				cast(s.custom_200000004 as date) dob,
				sg.short_name grade,
				cfso.code status,
				i.title immunization,
				cfle.log_field3 as \"error\"
			from custom_field_log_entries cfle
			join student_enrollment se
				on se.student_id = cfle.source_id and se.syear = (select syear from vars)
			join students s
				on s.student_id = cfle.source_id
			join schools sc
				on sc.id = se.school_id
			join school_gradelevels sg
				on sg.id = se.grade_id
			left join custom_field_select_options cfso
				on cfso.id = s.custom_630
			join imm_immunizations i
				on cast(i.id as varchar) = cfle.log_field1
			where cfle.log_field2 = ''No''
				and cfle.field_id = (select comp_field_id from imm)
				and coalesce(se.custom_9, ''N'') != ''Y''
				and (se.end_date is null or current_date between se.start_date and se.end_date)
				{IMM_DIST_SCHOOL} and se.school_id = (select school_id from vars)
				{IMM_TYPE}
			order by sc.custom_327, cast(replace(replace(sg.short_name, ''KG'', ''0.75''), ''PK'', ''0.5'') as float), concat(s.last_name, '', '', s.first_name), i.title;',
			'Record',
			'Records',
			'0',
			(select concat('||', min(id), '||') from user_profiles where type = 'super'),
			'N',
			(select id from custom_reports_folders where title = 'Immunizations' and parent_id = '#' and package = 'SIS'),
			'N',
			'N',
			'SIS',
			'This report shows all students who are currently out of compliance based on the immunization compliance rules.',
			'0');");
	}
}

// Setup Permissions
$results = Database::get(
	"with up as (
		select
			id
		from user_profiles
		where type = 'super'
	),
	fields as (
		select
			concat('SISStudent:', cf.id) gkey
		from custom_fields cf
		where cf.alias = 'immunization_compliance'
			and cf.type = 'log'
			and cf.source_class = 'SISStudent'
			and cf.deleted is null
	),
	permissions as (
		select
			perms
		from (
			values
				('can_view')
		) x (perms)
	),
	upfields as (
		select
			u.id profile_id,
			f.gkey
		from fields f
		cross join up u
	)
	select
		u.profile_id,
		concat(u.gkey, ':', p.perms) gkey
	from upfields u
	cross join permissions p
	left join permission pe
		on pe.profile_id = u.profile_id and pe.\"key\" = concat(u.gkey, ':', p.perms)
	where pe.id is null;"
);

$permissions = [];

foreach ($results as $result) {
	$profileId = $result["PROFILE_ID"];
	$key = $result["GKEY"];

	$permission = new Permission();
	$permission
		->setProfileId($profileId)
		->setKey($key);

	$permissions[] = $permission;
}

$results = Database::get(
	"with up as (
		select
			id
		from user_profiles
		where type = 'super'
	),
	fields as (
		select
			concat('SISStudent:', cf.id, '#', cflc.id) gkey
		from custom_fields cf
		join custom_field_log_columns cflc
			on cflc.field_id = cf.id
		where cf.alias = 'immunization_compliance'
			and cf.type = 'log'
			and cf.source_class = 'SISStudent'
			and cf.deleted is null
	),
	permissions as (
		select
			perms
		from (
			values
				('can_view')
		) x (perms)
	),
	upfields as (
		select
			u.id profile_id,
			f.gkey
		from fields f
		cross join up u
	)
	select
		u.profile_id,
		concat(u.gkey, ':', p.perms) gkey
	from upfields u
	cross join permissions p
	left join permission pe
		on pe.profile_id = u.profile_id and pe.\"key\" = concat(u.gkey, ':', p.perms)
	where pe.id is null;"
);

$permissions = [];

foreach ($results as $result) {
	$profileId = $result["PROFILE_ID"];
	$key = $result["GKEY"];

	$permission = new Permission();
	$permission
		->setProfileId($profileId)
		->setKey($key);

	$permissions[] = $permission;
}

$results = Database::get(
	"with up as (
		select
			id
		from user_profiles
		where type = 'super'
	),
	fields as (
		select
			concat('SISStudent:', cf.id) gkey
		from custom_fields cf
		where cf.alias = 'immunization_exemption'
			and cf.type = 'log'
			and cf.source_class = 'SISStudent'
			and cf.deleted is null
	),
	permissions as (
		select
			perms
		from (
			values
				('can_view'),
				('can_edit'),
				('can_delete'),
				('can_create')
		) x (perms)
	),
	upfields as (
		select
			u.id profile_id,
			f.gkey
		from fields f
		cross join up u
	)
	select
		u.profile_id,
		concat(u.gkey, ':', p.perms) gkey
	from upfields u
	cross join permissions p
	left join permission pe
		on pe.profile_id = u.profile_id and pe.\"key\" = concat(u.gkey, ':', p.perms)
	where pe.id is null;"
);

foreach ($results as $result) {
	$profileId = $result["PROFILE_ID"];
	$key = $result["GKEY"];

	$permission = new Permission();
	$permission
		->setProfileId($profileId)
		->setKey($key);

	$permissions[] = $permission;
}

$results = Database::get(
	"with up as (
		select
			id
		from user_profiles
		where type = 'super'
	),
	fields as (
		select
			concat('SISStudent:', cf.id, '#', cflc.id) gkey
		from custom_fields cf
		join custom_field_log_columns cflc
			on cflc.field_id = cf.id
		where cf.alias = 'immunization_exemption'
			and cf.type = 'log'
			and cf.source_class = 'SISStudent'
			and cf.deleted is null
	),
	permissions as (
		select
			perms
		from (
			values
				('can_view'),
				('can_edit'),
				('can_delete'),
				('can_create')
		) x (perms)
	),
	upfields as (
		select
			u.id profile_id,
			f.gkey
		from fields f
		cross join up u
	)
	select
		u.profile_id,
		concat(u.gkey, ':', p.perms) gkey
	from upfields u
	cross join permissions p
	left join permission pe
		on pe.profile_id = u.profile_id and pe.\"key\" = concat(u.gkey, ':', p.perms)
	where pe.id is null;"
);

foreach ($results as $result) {
	$profileId = $result["PROFILE_ID"];
	$key = $result["GKEY"];

	$permission = new Permission();
	$permission
		->setProfileId($profileId)
		->setKey($key);

	$permissions[] = $permission;
}

if ($permissions) {
	Permission::insert($permissions);
}

// Setup Categories
$results = Database::get(
	"with imm_col as (
		select
			id
		from custom_fields
		where legacy_id in (400000929, 929)
			and type = 'log'
			and source_class = 'SISStudent'
			and deleted is null
	),
	cat_col as (
		select
			cfjc.category_id
		from custom_fields_join_categories cfjc
		join imm_col ic
			on ic.id = cfjc.field_id
	),
	imm_add as (
		select
			id,
			case
				when alias = 'immunization_compliance' then 998
				when alias = 'immunization_exemption' then 999
			end sorder
		from custom_fields
		where alias in ('immunization_compliance', 'immunization_exemption')
	)
	select
		ia.id field_id,
		cc.category_id,
		ia.sorder sort_order
	from cat_col cc
	cross join imm_add ia
	left join custom_fields_join_categories cfjc
		on cfjc.field_id = ia.id and cfjc.category_id = cc.category_id
	where cfjc.id is null;"
);

$customFieldJoinCategories = [];
foreach ($results as $result) {
	$fieldId = $result["FIELD_ID"];
	$categoryId = $result["CATEGORY_ID"];
	$sortOrder = $result["SORT_ORDER"];

	$customFieldJoinCategory = new CustomFieldJoinCategory();
	$customFieldJoinCategory
		->setCategoryId($categoryId)
		->setFieldId($fieldId)
		->setSortOrder($sortOrder);

	$customFieldJoinCategories[] = $customFieldJoinCategory;
}

if ($customFieldJoinCategories) {
	CustomFieldJoinCategory::insert($customFieldJoinCategories);
	CustomFieldJoinCategory::fixSortOrders();
}

// Execute SQL
if (Database::$type === 'mssql') {
	Database::query("insert into edit_rules (id, name, enabled, category, sql, type)
	select
	  next value for edit_rules_seq,
	  concat('IMM - ', title),
	  1,
	  'SISStudent',
	  'exec sp_imm_run_all_comp @p_student_id = {STUDENT_ID}, @p_syear = {SYEAR}',
	  'sql'
	from custom_field_log_columns
	where (field_id = (select id from custom_fields where legacy_id in (400000929, 929) and type = 'log' and source_class = 'SISStudent' and deleted is null)
	  	and type = 'date'
	  	and title like '%[0-9]%'
	  	and deleted is null)
		or
			(field_id = (select id from custom_fields where alias = 'immunization_exemption' and type = 'log' and source_class = 'SISStudent' and deleted is null)
	  	and type = 'date'
	  	and column_name in ('LOG_FIELD3', 'LOG_FIELD4')
	  	and deleted is null);");

	Database::query("insert into edit_rule_criteria (id, field1, type, rule_id, reversed)
	select
	  next value for edit_rule_criteria_seq,
	  concat(cflc.field_id, '#', cflc.id),
	  cflc.type,
	  er.id,
	  1
	from custom_field_log_columns cflc
	join edit_rules er
	  on er.name = concat('IMM - ', cflc.title)
	where (cflc.field_id = (select id from custom_fields where legacy_id in (400000929, 929) and type = 'log' and source_class = 'SISStudent' and deleted is null)
	  	and cflc.type = 'date'
	  	and cflc.title like '%[0-9]%'
	  	and cflc.deleted is null)
		or
			(cflc.field_id = (select id from custom_fields where alias = 'immunization_exemption' and type = 'log' and source_class = 'SISStudent' and deleted is null)
			and cflc.type = 'date'
			and cflc.column_name in ('LOG_FIELD3', 'LOG_FIELD4')
			and cflc.deleted is null);");
} else if (Database::$type === 'postgres') {
	Database::query("insert into edit_rules (id, name, enabled, category, sql, type)
	select
	  nextval('edit_rules_seq'),
	  concat('IMM - ', title),
	  1,
	  'SISStudent',
	  (select concat('select ', string_agg(concat('fn_imm_calc(''', lower(code), ''', coalesce({STUDENT_ID}, 1), {SYEAR})'), ', '), ', fn_imm_calc_clean(coalesce({STUDENT_ID}, 1), {SYEAR});') from imm_immunizations),
	  'sql'
	from custom_field_log_columns
	where (field_id = (select id from custom_fields where legacy_id in (400000929, 929) and type = 'log' and source_class = 'SISStudent' and deleted is null)
	  	and type = 'date'
	  	and title ~ '[0-9]'
	  	and deleted is null)
		or
			(field_id = (select id from custom_fields where alias = 'immunization_exemption' and type = 'log' and source_class = 'SISStudent' and deleted is null)
			and type = 'date'
			and column_name in ('LOG_FIELD3', 'LOG_FIELD4')
			and deleted is null);");

	Database::query("insert into edit_rule_criteria (id, field1, type, rule_id, reversed)
	select
	  nextval('edit_rule_criteria_seq'),
	  concat(cflc.field_id, '#', cflc.id),
	  cflc.type,
	  er.id,
	  1
	from custom_field_log_columns cflc
	join edit_rules er
	  on er.name = concat('IMM - ', cflc.title)
	where (cflc.field_id = (select id from custom_fields where legacy_id in (400000929, 929) and type = 'log' and source_class = 'SISStudent' and deleted is null)
	  	and cflc.type = 'date'
	  	and cflc.title ~ '[0-9]'
	  	and cflc.deleted is null)
		or
			(cflc.field_id = (select id from custom_fields where alias = 'immunization_exemption' and type = 'log' and source_class = 'SISStudent' and deleted is null)
			and cflc.type = 'date'
			and cflc.column_name in ('LOG_FIELD3', 'LOG_FIELD4')
			and cflc.deleted is null);");
}

// Add SQL to populate immunizations compliance and exemptions drop down lists.
Database::query("update custom_field_log_columns set
	primary_sort = 1,
	option_query = 'with vars as (
		select {syear} syear
	)
	select distinct
		i.id as id,
		i.title as label
	from imm_immunizations i
	join imm_ruleset_groups_rulesets rgr
		on rgr.immunization_id = i.id
	join imm_ruleset_groups rg
		on rg.id = rgr.ruleset_group_id
	where rg.title = (select value from program_config where title = ''IMM_SELECTED_RULESET_GROUP'' and syear = (select cast(value as int) from program_config where program = ''system'' and title = ''DEFAULT_S_YEAR'' and syear is null and school_id is null))
		and (select syear from vars) >= start_dt
		and ((select syear from vars) <= end_dt or end_dt is null)'
	where field_id = (select id from custom_fields where alias = 'immunization_compliance') and column_name = 'LOG_FIELD1'");

Database::query("update custom_field_log_columns set
	primary_sort = 1,
	option_query = 'with vars as (
		select {syear} syear
	)
	select distinct
		i.id as id,
		i.title as label
	from imm_immunizations i
	join imm_ruleset_groups_rulesets rgr
		on rgr.immunization_id = i.id
	join imm_ruleset_groups rg
		on rg.id = rgr.ruleset_group_id
	where rg.title = (select value from program_config where title = ''IMM_SELECTED_RULESET_GROUP'' and syear = (select cast(value as int) from program_config where program = ''system'' and title = ''DEFAULT_S_YEAR'' and syear is null and school_id is null))
		and (select syear from vars) >= start_dt
		and ((select syear from vars) <= end_dt or end_dt is null)'
	where field_id = (select id from custom_fields where alias = 'immunization_exemption') and column_name = 'LOG_FIELD1'");
