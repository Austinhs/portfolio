<?php


// Add Daily Attendance Template
Database::query("delete
                  from importer_templates
                    where name = 'Daily Attendance';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'Daily Attendance\', \'main\', \'{"destinationTable":"attendance_day","temporaryTable":"attendanceDayTempImporter","errorTable":"attendanceDayTempImporter_error","primaryKeys":[],"identityColumn":""}\'
	);');

// Add Daily Attendance Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'attendance_day';");

//Create Pre-SQL Column if it does not exists
if (!Database::columnExists('importer_keys', 'pre_sql')) {
    Database::createColumn('importer_keys', 'pre_sql', 'text');
}

Database::query('INSERT INTO importer_keys (
	            table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS, PRE_SQL)
                VALUES
	            (\'attendance_day\', \'none\', \'["student_id","school_date"]\',\'["daily_code","syear"]\', \'["DELETE FROM attendancedaytempimporter WHERE row_id IN ( SELECT row_id FROM (SELECT row_id, ROW_NUMBER() OVER (PARTITION BY student_id, school_date ORDER BY row_id) AS rnum FROM attendancedaytempimporter) t WHERE t.rnum > 1);"]\' );
');

// Add Period Attendance Template
Database::query("delete
                  from importer_templates
                    where name = 'Period Attendance';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'Period Attendance\', \'main\', \'{"destinationTable":"attendance_period","temporaryTable":"attendancePeriodTempImporter","errorTable":"attendancePeriodTempImporter_error","primaryKeys":[],"identityColumn":""}\'
	);');

// Add Period Attendance Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'attendance_period';");


Database::query('INSERT INTO importer_keys (
	            table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS)
                VALUES
	            (\'attendance_period\', \'none\', \'["student_id","school_date","course_period_id"]\',\'["attendance_code","syear","course_id"]\' );
');