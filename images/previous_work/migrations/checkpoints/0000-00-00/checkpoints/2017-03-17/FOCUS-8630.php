<?php
//Delete Exisiting Fixed Width Split Import Settings
Database::query("delete
                  from importer_templates
                    where name = 'Create CSV File From Fixed Width File';");

Database::query("delete
                 from importer_keys
                 where table_name = 'fixed_width_importer_table';");

//Add Blank Table Import Settings
Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'Create CSV File From Fixed Width File\',
                  \'main\', \'{"destinationTable":"fixed_width_importer_table","temporaryTable":"fixed_width_importer_table_temp","errorTable":"fixed_width_importer_table_error","primaryKeys":[],"identityColumn":"none"}\'
	               );');

Database::query("INSERT INTO importer_keys (
	             TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS)
                 VALUES
	             ('fixed_width_importer_table', 'none', '[]','[]');");