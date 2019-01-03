<?php

$text_type     = Database::$type === 'postgres' ? 'TEXT' : 'VARCHAR(MAX)';

//Delete Exisiting Blank Table Import Settings
Database::query("delete
                  from importer_templates
                    where name = 'Test History Parser';");

Database::query("delete
                  from importer_templates
                      where name = 'File Parser';");            

//Add Blank Table Import Settings
Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'File Parser\',
                  \'main\', \'{"destinationTable":"importer_test_history_parser","temporaryTable":"importer_test_history_parser_temp","errorTable":"importer_test_history_parser_error","primaryKeys":[],"identityColumn":"none"}\'
	               );');


