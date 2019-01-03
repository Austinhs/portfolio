<?php

Database::query("
ALTER TABLE student_report_card_grades_change_requests
ADD course_section varchar(25)
");

Database::query("
ALTER TABLE student_report_card_grades_change_requests
ADD status_by varchar(100)
");

if (Database::$type === 'mssql') {
	Database::query(
	"ALTER TABLE student_report_card_grades_change_requests
	ADD status_date DateTime
	");
} else {
	Database::query("
	ALTER TABLE student_report_card_grades_change_requests
	ADD status_date timestamp
	");
}
