<?php

if (!Database::columnExists('school_choice_program_seats', 'all_grades')) {
	Database::createColumn('school_choice_program_seats', 'all_grades', 'numeric');
}
