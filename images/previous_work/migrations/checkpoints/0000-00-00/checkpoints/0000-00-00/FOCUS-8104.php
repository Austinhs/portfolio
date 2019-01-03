<?php

if(!Database::sequenceExists('gl_hr_injury_report_case_number_seq')) {
	Database::createSequence('gl_hr_injury_report_case_number_seq');
}
