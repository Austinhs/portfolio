<?php
//Delete Exisiting Fixed Width Split Import Settings
Database::query("delete
                  from importer_templates
                    where name = 'Match Students';");

Database::query("delete
                 from importer_keys
                 where table_name = 'match_students_importer_table';");

//Add Blank Table Import Settings
Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'Match Students\',
                  \'main\', \'{"destinationTable":"match_students_importer_table","temporaryTable":"match_students_importer_table_temp","errorTable":"match_students_importer_table_error","primaryKeys":[],"identityColumn":"none"}\'
	               );');

if (Database::tableExists('match_students_importer_table')){
    Database::query('drop table match_students_importer_table;');
}


Database::query("INSERT INTO importer_keys (
	             TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS)
                 VALUES
	             ('match_students_importer_table', 'none', '[]','[\"first_name\",\"last_name\",\"birth_date\"]');");
Database::query('
	CREATE TABLE match_students_importer_table(
	school varchar(255),
	first_name varchar(255),
	last_name varchar(255),
	birth_date date,
	line_number numeric(32),
	file_id numeric(32),
	local_id varchar(255),
	file_name varchar(255),
	file_type varchar(255),
	original_file_name varchar(255),
	header numeric(32)
);
');
