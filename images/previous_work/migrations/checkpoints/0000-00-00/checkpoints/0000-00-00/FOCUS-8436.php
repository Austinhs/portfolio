<?php
// Tags: Formbuilder
Migrations::depend('FOCUS-9271');

if (!Database::tableExists('formbuilder_tags')) {
	Database::createSequence('formbuilder_tags_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_tags (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_tags_id_seq}},
			title VARCHAR(255)
		)
	"));
}

if (!Database::tableExists('formbuilder_join_tags')) {
	Database::createSequence('formbuilder_join_tags_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_join_tags (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_join_tags_id_seq}},
			form_id BIGINT REFERENCES sss_forms(id) ON DELETE CASCADE,
			tag_id BIGINT REFERENCES formbuilder_tags(id)
		)
	"));
}

if (!Database::tableExists('formbuilder_join_profiles')) {
	Database::createSequence('formbuilder_join_profiles_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_join_profiles (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_join_profiles_id_seq}},
			form_id BIGINT REFERENCES sss_forms(id) ON DELETE CASCADE,
			profile_id NUMERIC REFERENCES user_profiles(id)
		)
	"));
}
