<?php

if(!Database::tableExists('edit_rule_profiles')) {
	Database::query("
		CREATE TABLE edit_rule_profiles (
			id BIGINT PRIMARY KEY,
			profile_id BIGINT NOT NULL,
			rule_id BIGINT NOT NULL
		)
	");
}

if(!Database::sequenceExists('edit_rule_profiles_seq')) {
	Database::createSequence('edit_rule_profiles_seq');
}
