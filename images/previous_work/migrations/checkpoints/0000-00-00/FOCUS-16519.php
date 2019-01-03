<?php

	$table = 'two_factor_auth';

	if(!Database::tableExists($table)) {
		$query = "
			CREATE TABLE {$table} (
				id BIGINT PRIMARY KEY,
				type VARCHAR(255),
				username VARCHAR(255) UNIQUE,
				secret VARCHAR(255) UNIQUE,
				active BIGINT
			)
		";

		Database::query($query);
		Database::createSequence('two_factor_auth_seq');
	}