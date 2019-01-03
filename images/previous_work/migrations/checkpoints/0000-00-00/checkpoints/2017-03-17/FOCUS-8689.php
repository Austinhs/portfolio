<?php

$datetime_type = Database::$type === 'postgres' ? 'TIMESTAMP' : 'DATETIME2';

if(!Database::columnExists('school_choice_application_status', 'app_type')){
	Database::createColumn('school_choice_application_status', 'app_type', 'varchar', '50');
}
if(!Database::columnExists('school_choice_application_status', 'last_modified_user')){
	Database::createColumn('school_choice_application_status', 'last_modified_user', 'numeric', '10');
}
if(!Database::columnExists('school_choice_application_status', 'last_modified_date')){
	Database::createColumn('school_choice_application_status', 'last_modified_date', $datetime_type);
}
if(!Database::columnExists('school_choice_application_status', 'status_date')){
	Database::createColumn('school_choice_application_status', 'status_date', $datetime_type);
}
if(!Database::columnExists('school_choice_application_status', 'reason')){
	Database::createColumn('school_choice_application_status', 'reason', 'varchar');
}
