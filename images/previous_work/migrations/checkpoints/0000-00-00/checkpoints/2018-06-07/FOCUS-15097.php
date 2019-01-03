<?php

// Tags: Formbuilder

if (Database::tableExists('sss_form_collections')) {
	Database::query("DROP TABLE sss_form_collections");
}

if (Database::tableExists('sss_form_drafts')) {
	Database::query("DROP TABLE sss_form_drafts");
	if (Database::sequenceExists('sss_form_drafts_id_seq')) {
		Database::dropSequence('sss_form_drafts_id_seq');
	}

	Database::createSequence('formbuilder_drafts_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_drafts (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_drafts_id_seq}},
			form_id BIGINT REFERENCES formbuilder_forms(id),
			form TEXT
		)
	"));
}
