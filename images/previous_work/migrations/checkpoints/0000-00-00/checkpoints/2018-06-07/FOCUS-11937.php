<?php

if (!Database::sequenceExists('school_choice_application_status_seq')) {
	$max_id = Database::get("SELECT MAX(id) FROM school_choice_application_status;");
	Database::createSequence('school_choice_application_status_seq', $max_id[0]['MAX'] + 1);
}


