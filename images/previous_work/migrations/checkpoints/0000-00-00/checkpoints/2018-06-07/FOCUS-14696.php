<?php

if(!Database::tableExists('enrollment_restriction')) {
	Database::query("CREATE TABLE enrollment_restriction (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('enrollment_restriction_seq')) {
	Database::createSequence('enrollment_restriction_seq');
}

if(!Database::columnExists('enrollment_restriction', 'student_id')) {
	Database::createColumn('enrollment_restriction', 'student_id', 'BIGINT');
}

if(!Database::columnExists('enrollment_restriction', 'active')) {
	Database::createColumn('enrollment_restriction', 'active', 'BIGINT');
}

if(!Database::indexExists('enrollment_restriction', 'enrollment_restriction_active')) {
	Database::query('CREATE INDEX enrollment_restriction_active ON enrollment_restriction (active)');
}

if(!Database::columnExists('enrollment_restriction', 'start_date')) {
	Database::createColumn('enrollment_restriction', 'start_date', 'DATE');
}

if(!Database::indexExists('enrollment_restriction', 'enrollment_restriction_start_date')) {
	Database::query('CREATE INDEX enrollment_restriction_start_date ON enrollment_restriction (start_date)');
}

if(!Database::columnExists('enrollment_restriction', 'end_date')) {
	Database::createColumn('enrollment_restriction', 'end_date', 'DATE');
}

if(!Database::indexExists('enrollment_restriction', 'enrollment_restriction_end_date')) {
	Database::query('CREATE INDEX enrollment_restriction_end_date ON enrollment_restriction (end_date)');
}

if(!Database::columnExists('enrollment_restriction', 'comments')) {
	Database::createColumn('enrollment_restriction', 'comments', 'TEXT');
}

if(!Database::columnExists('enrollment_restriction', 'deleted')) {
	Database::createColumn('enrollment_restriction', 'deleted', 'BIGINT');
}

if(!Database::indexExists('enrollment_restriction', 'enrollment_restriction_deleted')) {
	Database::query('CREATE INDEX enrollment_restriction_deleted ON enrollment_restriction (deleted)');
}
