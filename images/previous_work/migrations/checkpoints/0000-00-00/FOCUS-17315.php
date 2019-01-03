<?php

if(Database::columnExists('grade_posting_term_weights', 'id')) {
	$sequence = 'grade_posting_term_weights_seq';

	if(!Database::sequenceExists($sequence)) {
		Database::createSequence($sequence);
	}

	$sequence_sql = Database::nextSql($sequence);

	$query = "
		UPDATE
			grade_posting_term_weights
		SET
			id = {$sequence_sql}
		WHERE
			id IS NULL
	";

	Database::query($query);
}
