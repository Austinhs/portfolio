<?php

if(Database::$type === 'mssql') {
	Database::query("ALTER TABLE cron_jobs ALTER COLUMN hour SMALLINT NULL");
	Database::query("ALTER TABLE cron_jobs ALTER COLUMN minute SMALLINT NULL");
}
if(!Database::columnExists('portal_pages', 'main')) {
	Database::createColumn('portal_pages', 'main', 'numeric');
}

// This seemed to be too slow on many sites.
// We will just have to add it manually for now.

// if(!Database::indexExists('student_report_card_grades', 'student_report_card_grades_ind8')) {
// 	$sql = Database::preprocess("
// 		CREATE INDEX {{postgres:CONCURRENTLY}}
// 			student_report_card_grades_ind8
// 		ON
// 			student_report_card_grades(course_id)
// 	");

// 	Database::isolate(function() use($sql) {
// 		Database::query($sql);
// 	});
// }
