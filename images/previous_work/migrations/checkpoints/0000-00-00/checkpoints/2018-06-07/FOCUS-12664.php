<?php

if (!Database::columnExists("user_audit_trail", "page_load_time")) {
	Database::createColumn("user_audit_trail", "page_load_time", "numeric(6,2)");
}

if (!Database::columnExists("schools", "custom_327")) {
	Database::createColumn("schools", "custom_327", "varchar(10)");
}

if (!Database::columnExists("course_periods", "custom_17")) {
	Database::createColumn("course_periods", "custom_17", "varchar(20)");
}

if(!Database::columnExists('students','custom_200000015')) {
	Database::createColumn('students','custom_200000015','varchar(20)');
}
?>
