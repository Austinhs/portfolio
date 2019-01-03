<?php

if (!Database::sequenceExists('focus_table_records_id_seq')) {
	Database::createSequence('focus_table_records_id_seq');
}

if (!Database::tableExists('focus_tables')) {
	Database::query("
		CREATE TABLE focus_tables (
			table_name VARCHAR(255) PRIMARY KEY NOT NULL,
			id_column  VARCHAR(255) NOT NULL DEFAULT 'id',
			generated_columns TEXT
		);
	");
}

if (!Database::tableExists('focus_table_records')) {
	Database::query(Database::preprocess("
		 CREATE TABLE focus_table_records (
			id NUMERIC PRIMARY KEY DEFAULT {{next:focus_table_records_id_seq}},
			table_name VARCHAR(255) NOT NULL,
			record_id  NUMERIC NOT NULL
		)
	"));
}
