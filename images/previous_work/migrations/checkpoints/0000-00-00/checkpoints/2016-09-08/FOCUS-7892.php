<?php


// Add Course History Template
Database::query("delete
                  from importer_templates
                    where name = 'Course History';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'Course History\', \'main\', \'{"destinationTable":"student_report_card_grades","temporaryTable":"studentReportCardGradesTempTable","errorTable":"studentReportCardGradesTempTable_error","primaryKeys":[],"identityColumn":""}\'
	);');

// Add Course History Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'student_report_card_grades';");


Database::query('INSERT INTO importer_keys (
	            table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS)
                VALUES
	            (\'student_report_card_grades\', \'id\', \'["student_id","marking_period_id","syear","course_num","school_id"]\',\'[]\' );
');