<?php
// Tags: Formbuilder
if (Database::$type === 'postgres') {
	$text      = 'text';
	$timestamp = 'timestamp without time zone';
} else {
	$text      = 'varchar(max)';
	$timestamp = 'datetime2(6)';
}

// return false;
if (!Database::tableExists('formbuilder_forms')) {
	Database::createSequence('formbuilder_forms_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_forms (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_forms_id_seq}} NOT NULL,
			name VARCHAR(255) NOT NULL,
			head_revision BIGINT,
			language_id BIGINT NOT NULL,
			folder VARCHAR(255),
			deleted_at DATE
		)
	"));

	Database::query("CREATE INDEX formbuilder_forms_name_ind ON formbuilder_forms(name)");
}

if (!Database::tableExists('formbuilder_revisions')) {
	Database::createSequence('formbuilder_revisions_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_revisions (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_revisions_id_seq}} NOT NULL,
			form_id BIGINT REFERENCES formbuilder_forms(id) ON DELETE CASCADE NOT NULL,
			author_id NUMERIC REFERENCES users(staff_id) NOT NULL,
			hash VARCHAR(40) NOT NULL,
			revised_at {$timestamp} NOT NULL
		)
	"));

	Database::query("ALTER TABLE formbuilder_revisions ADD UNIQUE(form_id, hash)");
	Database::query("ALTER TABLE formbuilder_forms ADD FOREIGN KEY (head_revision) REFERENCES formbuilder_revisions(id)");
}

if (!Database::tableExists('formbuilder_objects')) {
	Database::createSequence('formbuilder_objects_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_objects (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_objects_id_seq}} NOT NULL,
			hash VARCHAR(40) UNIQUE,
			object {$text}
		);
	"));
}

if (!Database::tableExists('formbuilder_components')) {
	Database::createSequence('formbuilder_components_id_seq');

	// Inferior MS SQL is SOL
	$delete_on_casade = Database::$type === "postgres" ? "ON DELETE CASCADE" : "";
	$delete_set_null  = Database::$type === "postgres" ? "ON DELETE SET NULL" : "";


	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_components (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_components_id_seq}},
			type VARCHAR(255) NOT NULL,
			name VARCHAR(255),
			required INTEGER,
			form_id BIGINT REFERENCES formbuilder_forms(id) ON DELETE CASCADE NOT NULL,
			parent_id BIGINT REFERENCES formbuilder_components(id) {$delete_on_casade},
			model_id BIGINT REFERENCES formbuilder_objects(id) NOT NULL,
			layout_id BIGINT REFERENCES formbuilder_objects(id),
			options_id BIGINT REFERENCES formbuilder_objects(id),
			created_revision BIGINT REFERENCES formbuilder_revisions(id) {$delete_on_casade} NOT NULL,
			removed_revision BIGINT REFERENCES formbuilder_revisions(id) {$delete_set_null}
		)
	"));

	Database::query("CREATE INDEX formbuilder_components_form_id_ind ON formbuilder_components(form_id)");
	Database::query("CREATE INDEX formbuilder_components_created_revision_ind ON formbuilder_components(created_revision)");
	Database::query("CREATE INDEX formbuilder_components_removed_revision_ind ON formbuilder_components(removed_revision)");
	Database::query("CREATE INDEX formbuilder_components_name_ind ON formbuilder_components(name)");
}

if (!Database::tableExists('formbuilder_instances')) {
	Database::createSequence('formbuilder_instances_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_instances (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_instances_id_seq}} NOT NULL,
			form_id BIGINT REFERENCES formbuilder_forms(id) NOT NULL,
			author_id NUMERIC REFERENCES users(staff_id),
			revision_id BIGINT REFERENCES formbuilder_revisions(id) NOT NULL,
			server_data {$text}
		)
	"));
}

if (!Database::tableExists('formbuilder_data')) {
	Database::createSequence('formbuilder_data_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_data (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_data_id_seq}} NOT NULL,
			instance_id BIGINT REFERENCES formbuilder_instances(id) ON DELETE CASCADE NOT NULL,
			component_id BIGINT REFERENCES formbuilder_components(id) NOT NULL,
			value {$text}
		)
	"));
}

if (!Database::tableExists('formbuilder_collections')) {
	Database::createSequence('formbuilder_collections_seq');

	// Inferior MS SQL is SOL
	$delete_on_casade = Database::$type === "postgres" ? "ON DELETE CASCADE" : "";

	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_collections (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_collections_seq}} NOT NULL,
			form_id BIGINT REFERENCES formbuilder_forms(id) NOT NULL,
			created_revision BIGINT REFERENCES formbuilder_revisions(id) {$delete_on_casade} NOT NULL,
			removed_revision BIGINT REFERENCES formbuilder_revisions(id) ON DELETE SET NULL,
			name VARCHAR(255) NOT NULL,
			query {$text} NOT NULL
		)
	"));
}

if (!Database::columnExists('sss_form_instances', 'instance_id')) {
	Database::createColumn('sss_form_instances', 'instance_id', 'BIGINT');
}

if (!Database::columnExists('sss_form_instances', 'draft_instance_id')) {
	Database::createColumn('sss_form_instances', 'draft_instance_id', 'BIGINT');
}

if (!Database::columnExists('sss_forms', 'new_form_id')) {
	Database::createColumn('sss_forms', 'new_form_id', 'BIGINT');
}

$constraints = Database::getConstraints('sss_form_instances');
if (isset($constraints['sss_form_instances_form_id_foreign'])) {
	Database::query("ALTER TABLE sss_form_instances DROP CONSTRAINT sss_form_instances_form_id_foreign");
}
