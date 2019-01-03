<?php

$text_type     = Database::$type === 'postgres' ? 'TEXT' : 'VARCHAR(MAX)';

//Delete Exisiting Blank Table Import Settings
Database::query("delete
                  from importer_templates
                    where name = 'Test History Parser';");

Database::query("delete
                 from importer_keys
                 where table_name = 'importer_test_history_parser';");

//Add Blank Table Import Settings
Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'Test History Parser\',
                  \'main\', \'{"destinationTable":"importer_test_history_parser","temporaryTable":"importer_test_history_parser_temp","errorTable":"importer_test_history_parser_error","primaryKeys":[],"identityColumn":"none"}\'
	               );');

Database::query("INSERT INTO importer_keys (
	             TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS)
                 VALUES
                 ('importer_test_history_parser', 'none', '[]','[]');");


//Create Student Conatcts Importer Table 
if(!Database::tableExists('importer_test_history_parsed')){
	Database::query("CREATE TABLE importer_test_history_parsed(
                        STDT_ID {$text_type},
                        TEST_CD {$text_type},
                        DT_ADMIN {$text_type},
                        SCH_YR {$text_type},
                        GRDE_LVL {$text_type},
                        LEP_INFO {$text_type},
                        INCLD_TRANSCRIPT {$text_type},
                        DJJ_INFO {$text_type},
                        TEST_LVL {$text_type},
                        TEST_FORM {$text_type},
                        DISTRICT_ADMIN {$text_type},
                        SCH_ADMIN {$text_type},
                        TEST_PUB_YR {$text_type},
                        PART_ID {$text_type},
                        SCORE_TYPE_ID {$text_type},
                        SCORE {$text_type},
                        FIRST_NAME {$text_type},
                        LAST_NAME  {$text_type},
                        BIRTH_DATE {$text_type}
					);");
}

if(!Database::tableExists('IMPORTER_TEST_HISTORY_PARSER_TEMPLATES')){
  if(Database::$type === 'postgres'){
    Database::query("   CREATE TABLE IMPORTER_TEST_HISTORY_PARSER_TEMPLATES(
      id SERIAL PRIMARY KEY NOT NULL,
      NAME {$text_type},
      PARSE_LOGIC {$text_type}
    );");
  }
  else {
    Database::query("   CREATE TABLE IMPORTER_TEST_HISTORY_PARSER_TEMPLATES(
      id bigint IDENTITY(1,1) PRIMARY KEY,
      NAME {$text_type},
      PARSE_LOGIC {$text_type}
    );");
  }
}