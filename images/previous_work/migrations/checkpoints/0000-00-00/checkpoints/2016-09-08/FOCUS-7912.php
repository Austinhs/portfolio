<?php


// Add Course History Template
Database::query("delete
                  from importer_templates
                    where name = 'User Enrollment';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'User Enrollment\', \'main\', \'{"destinationTable":"user_enrollment","temporaryTable":"userenrollmentTempImporter","errorTable":"userenrollmentTempImporter_error","primaryKeys":[],"identityColumn":""}\'
	);');

// Add Course History Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'user_enrollment';");


Database::query('INSERT INTO importer_keys (
	            table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS)
                VALUES
	            (\'user_enrollment\', \'id\', \'["staff_id","schools","profiles"]\',\'[]\' );
');