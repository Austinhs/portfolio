<?php

// Update Resource To Rooms
Database::query("update importer_templates
                 set name = 'Rooms'
                 where type = 'main' and name = 'Resources';");

//Delete Exisiting Blank Table Import Settings
Database::query("delete
                  from importer_templates
                    where name = 'Import File Into Blank Table';");

Database::query("delete
                 from importer_keys
                 where table_name = 'user_defined_table';");

//Add Blank Table Import Settings
Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'Import File Into Blank Table\',
                  \'main\', \'{"destinationTable":"user_defined_table","temporaryTable":"user_defined_table_temp","errorTable":"user_defined_table_error","primaryKeys":[],"identityColumn":"none"}\'
	               );');

Database::query("INSERT INTO importer_keys (
	             TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS)
                 VALUES
	             ('user_defined_table', 'none', '[]','[]');");

//Create Post-SQL Column if it does not exists 
if (!Database::columnExists('importer_keys', 'post_sql')) {
    Database::createColumn('importer_keys', 'post_sql', 'text');
}



