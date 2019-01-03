<?php

// Add Test History Template

Database::query("delete
                  from importer_templates
                    where name = 'Test History';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
                 \'Test History\', \'main\', \'{"destinationTable":"test_history_importer_table","temporaryTable":"test_history_importer_tableTempImporter","errorTable":"test_history_importer_tableTempImporter_error","primaryKeys":[],"identityColumn":""}\'
    );');

// Add Test History Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'test_history_importer_table';");


 Database::query("INSERT INTO importer_keys (
                table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS)
                VALUES
                ('test_history_importer_table','none','[]','[\"student_id\",\"syear\",\"grade\",\"date_admin\",\"school_id\",\"district\",\"test_id\",\"part_id\",\"incl_in_transc\",\"test_pub_yr\",\"score_type\",\"score_value\"]')");

//Create Student Contacts Importer Table 
if(!Database::tableExists('TEST_HISTORY_IMPORTER_TABLE')){
	Database::query("CREATE TABLE TEST_HISTORY_IMPORTER_TABLE(
					SYEAR NUMERIC,
					STUDENT_ID NUMERIC,
					GRADE NUMERIC,
					DATE_ADMIN DATE,
					SCHOOL_ID NUMERIC,
					DISTRICT VARCHAR(255),
					TEST_ID NUMERIC,
					PART_ID NUMERIC,
					TEST_LEVEL VARCHAR(255),
					TEST_FORM VARCHAR(255),
					INCL_IN_TRANSC VARCHAR(255),
					TEST_PUB_YR NUMERIC,
					SCORE_TYPE NUMERIC,
					SCORE_VALUE VARCHAR(255),
					ADMIN_ID NUMERIC,
					DATA_TYPE VARCHAR(255)
					);");
}
