<?php

// Add Schedule Requests Template

Database::query("delete
                  from importer_templates
                    where name = 'Schedule Requests';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
                 \'Schedule Requests\', \'main\', \'{"destinationTable":"schedule_requests","temporaryTable":"schedule_requestsTempImporter","errorTable":"schedule_requestsTempImporter_error","primaryKeys":[],"identityColumn":""}\'
    );');

// Add Schedule Requests Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'schedule_requests';");


 Database::query("INSERT INTO importer_keys (
                table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS)
                VALUES
                ('schedule_requests','request_id','[\"student_id\",\"syear\",\"school_id\",\"course_id\"]','[]')");

// Add Attendance Calandars Template
Database::query("delete
                  from importer_templates
                    where name = 'Attendance Calander';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
                 \'Attendance Calander\', \'main\', \'{"destinationTable":"attendance_calendars","temporaryTable":"attendance_calendarsTempImporter","errorTable":"attendance_calendarsTempImporter_error","primaryKeys":[],"identityColumn":""}\'
    );');

// Add Attendance Calandars Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'attendance_calendars';");


 Database::query("INSERT INTO importer_keys (
                table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS)
                VALUES
                ('attendance_calendars', 'calendar_id', '[\"syear\",\"school_id\",\"title\"]','[\"minutes_for_import\",\"school_date_for_import\"]')");