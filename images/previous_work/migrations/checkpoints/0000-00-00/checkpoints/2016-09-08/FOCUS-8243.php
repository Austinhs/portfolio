<?php

//Get rid of un-needed student enrollment post_sql
Database::query("update importer_keys set post_sql = NULL where table_name = 'student_enrollment';");