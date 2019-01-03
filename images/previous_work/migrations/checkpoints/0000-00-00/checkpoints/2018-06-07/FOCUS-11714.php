<?php

if (!Database::sequenceExists('applicant_username_seq')) {
	Database::createSequence('applicant_username_seq');
}