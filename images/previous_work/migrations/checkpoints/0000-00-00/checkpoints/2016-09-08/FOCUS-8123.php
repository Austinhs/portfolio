<?php


// Alter Courses Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'courses';");


Database::query("INSERT INTO importer_keys (TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS, PRE_SQL, POST_SQL) values('courses', 'course_id', '[\"school_id\",\"title\",\"short_name\",\"syear\"]', '[\"credits_for_import\",\"subject_id\"]','[\"alter table courses add credits_for_import varchar\"]', '[\"
INSERT INTO COURSE_WEIGHTS (
  COURSE_ID, SCHOOL_ID, SYEAR, COURSE_WEIGHT, 
  CREDITS, IMPORTED
) 
SELECT 
  COURSE_ID, 
  SCHOOL_ID, 
  SYEAR, 
  ''1'',
  CAST(credits_for_import AS INT), 
  ''I''
FROM 
  courses 
WHERE 
  credits_for_import IS NOT NULL;\",\"alter table courses drop credits_for_import;\",
  \"
  delete from course_weights where course_id in (select course_id from courses where credits_for_import is not null)
  \"
  ]')");

//Alter Course Periods Template
Database::query("delete
                 from importer_keys
                where table_name = 'course_periods'");

Database::query("INSERT INTO importer_keys ( TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS)
                  values( 'course_periods', 'course_period_id', '[\"school_id\",\"course_id\",\"short_name\"]', '[\"syear\",\"grade_posting_scheme_id\"]')");