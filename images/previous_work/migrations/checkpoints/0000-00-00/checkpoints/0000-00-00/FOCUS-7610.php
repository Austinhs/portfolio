<?php

// Create table if needed
if(!Database::tableExists('signer_join_signatures')) {
	$sql = "
		CREATE TABLE signer_join_signatures (
			id INTEGER PRIMARY KEY,
			module_name VARCHAR(255) NULL,
			context_table VARCHAR(63) NULL,
			context_id VARCHAR(127) NULL,
			context_secondary VARCHAR(63) NULL,
			signer_table VARCHAR(63) NULL,
			signer_id VARCHAR(127) NULL,
			purpose VARCHAR(31) NOT NULL,
			signature_id INTEGER NULL
		)
	";
	Database::query($sql);
}

// Create sequence if needed
if (!Database::sequenceExists('signer_join_signatures_seq')) {
	Database::createSequence('signer_join_signatures_seq');
}