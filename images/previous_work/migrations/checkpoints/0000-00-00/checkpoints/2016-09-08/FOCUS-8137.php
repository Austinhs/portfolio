<?php


// Alter Courses Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'student_enrollment';");

if (Database::$type === 'mssql'){
  Database::query("INSERT INTO importer_keys ( TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS, POST_SQL) values( 'student_enrollment', 'id', '[\"student_id\",\"school_id\",\"syear\"]', '[\"start_date\",\"include_in_class_rank\"]','[\"

              UPDATE 
                STUDENT_ENROLLMENT 
              SET 
                CALENDAR_ID = (
                  SELECT TOP 1
                    CALENDAR_ID 
                  FROM 
                    ATTENDANCE_CALENDARS AC 
                  WHERE 
                    AC.SYEAR = STUDENT_ENROLLMENT.SYEAR 
                    AND AC.SCHOOL_ID = STUDENT_ENROLLMENT.SCHOOL_ID 
                    order by default_calendar asc
                ) 
              WHERE 
                CALENDAR_ID = 0

  \"]');
");
}
else{
Database::query("INSERT INTO importer_keys ( TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS, POST_SQL) values( 'student_enrollment', 'id', '[\"student_id\",\"school_id\",\"syear\"]', '[\"start_date\",\"include_in_class_rank\"]','[\"

              UPDATE 
                STUDENT_ENROLLMENT 
              SET 
                CALENDAR_ID = (
                  SELECT 
                    CALENDAR_ID 
                  FROM 
                    ATTENDANCE_CALENDARS AC 
                  WHERE 
                    AC.SYEAR = STUDENT_ENROLLMENT.SYEAR 
                    AND AC.SCHOOL_ID = STUDENT_ENROLLMENT.SCHOOL_ID 
                    order by default_calendar asc
                  LIMIT 
                    1
                ) 
              WHERE 
                CALENDAR_ID = 0

  \"]');
");
}

