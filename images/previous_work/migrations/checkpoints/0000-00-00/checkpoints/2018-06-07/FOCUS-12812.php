<?php

Database::query("delete from STUDENTS_JOIN_STUDENTS where
	not exists (select '' from students s where s.student_id=STUDENTS_JOIN_STUDENTS.PRIMARY_STUDENT_ID)
	or
	not exists (select '' from students s where s.student_id=STUDENTS_JOIN_STUDENTS.SECONDARY_STUDENT_ID)
");

