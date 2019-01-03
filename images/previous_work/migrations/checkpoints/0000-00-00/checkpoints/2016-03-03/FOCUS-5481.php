<?php

Migrations::depend('FOCUS-6359');

// Create the profile_ids and gradelevels columns if they don't exist
if(!Database::columnExists('student_enrollment_codes', 'profile_ids')) {
	Database::createColumn('student_enrollment_codes', 'profile_ids', 'text');
}

if(!Database::columnExists('student_enrollment_codes', 'gradelevels')) {
	Database::createColumn('student_enrollment_codes', 'gradelevels', 'text');
}

// Drop the profiles column if it exists (used to be created in
// StudentEnrollment::upgradeStudentEnrollments but was never used)
if(Database::columnExists('student_enrollment_codes', 'profiles')) {
	Database::query("ALTER TABLE student_enrollment_codes DROP COLUMN profiles");
}
