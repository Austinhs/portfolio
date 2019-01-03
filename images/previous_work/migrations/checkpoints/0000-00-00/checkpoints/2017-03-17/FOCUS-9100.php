<?php

if (empty(Database::columnExists('student_report_card_grades_change_requests', 'reason_code'))){
	Database::createColumn('student_report_card_grades_change_requests', 'reason_code', 'varchar', '100');
}

?>