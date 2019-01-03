<?php

Migrations::depend('FOCUS-5468');

if(!Database::columnExists('students_form_records', 'language')) {
	Database::createColumn('students_form_records', 'language', 'numeric');
}

if(!Database::columnExists('students_form_records', 'finalized')) {
	Database::createColumn('students_form_records', 'finalized', 'char');
}
