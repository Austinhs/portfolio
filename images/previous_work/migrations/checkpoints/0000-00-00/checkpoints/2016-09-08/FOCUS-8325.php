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
                ('schedule_requests', '[\"student_id\",\"syear\",\"school_id\",\"course_id\"]', '[]','[]')");