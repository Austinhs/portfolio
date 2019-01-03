<?php

if(!Database::tableExists('external_api_schema_restriction')) {
	Database::query("CREATE TABLE external_api_schema_restriction (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('external_api_schema_restriction_seq')) {
	Database::createSequence('external_api_schema_restriction_seq');
}

if(!Database::columnExists('external_api_schema_restriction', 'external_api_id')) {
	Database::createColumn('external_api_schema_restriction', 'external_api_id', 'BIGINT');
}

if(!Database::columnExists('external_api_schema_restriction', 'result_schema')) {
	Database::createColumn('external_api_schema_restriction', 'result_schema', 'VARCHAR(255)');
}
