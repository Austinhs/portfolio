<?php
if(!Database::columnExists('attendance_period', 'admin_user_id')) {
	Database::createColumn('attendance_period', 'admin_user_id', 'numeric');
}
if(!Database::columnExists('attendance_day', 'admin_user_id')) {
	Database::createColumn('attendance_day', 'admin_user_id', 'numeric');
}
if(!Database::columnExists('students_changelog', 'admin_user_id')) {
	Database::createColumn('students_changelog', 'admin_user_id', 'numeric');
}