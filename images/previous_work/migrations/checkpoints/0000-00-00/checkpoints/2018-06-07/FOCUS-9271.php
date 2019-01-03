<?php
// Tags: Formbuilder
if (!function_exists('keyword')) {
	function keyword($column) {
		if (Database::$type === 'postgres') {
			return "\"{$column}\"";
		} else {
			return "[{$column}]";
		}
	}
}

if (Database::$type === 'postgres') {
	$text      = 'text';
	$timestamp = 'timestamp without time zone';
	$boolean   = 'boolean';
	$true      = 'true';
	$false     = 'false';
} else {
	$text      = 'varchar(max)';
	$identity  = 'IDENTITY';
	$timestamp = 'datetime2(6)';
	$boolean   = 'bit';
	$true      = '1';
	$false     = '0';
}

if (!Database::tableExists('sss_forms')) {
	Database::query("
		CREATE TABLE sss_forms (
			id bigint {$identity} primary key NOT NULL,
			layout {$text} NOT NULL,
			name varchar(255) NOT NULL,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			max_supplements bigint,
			tags {$text},
			language_id numeric DEFAULT (1) NOT NULL,
			clone_source bigint,
			folder varchar(255) DEFAULT 'Root' NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_forms_id_seq');
		Database::query('ALTER SEQUENCE sss_forms_id_seq OWNED BY sss_forms.id');
		Database::query("ALTER TABLE sss_forms ALTER COLUMN id SET DEFAULT nextval('sss_forms_id_seq')");
	}
	Database::query("CREATE INDEX sss_forms_language_id_ind ON sss_forms (language_id)");
}

if (!Database::tableExists('sss_form_drafts')) {
	Database::query("
		CREATE TABLE sss_form_drafts (
			id bigint {$identity} primary key NOT NULL,
			form_id bigint,
			form {$text} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_form_drafts_id_seq');
		Database::query('ALTER SEQUENCE sss_form_drafts_id_seq OWNED BY sss_form_drafts.id');
		Database::query("ALTER TABLE sss_form_drafts ALTER COLUMN id SET DEFAULT nextval('sss_form_drafts_id_seq')");
	}
	Database::query("ALTER TABLE sss_form_drafts ADD CONSTRAINT sss_form_drafts_form_id_unique UNIQUE (form_id)");
	Database::query("ALTER TABLE sss_form_drafts ADD CONSTRAINT sss_form_drafts_form_id_foreign FOREIGN KEY (form_id) REFERENCES sss_forms(id);");
}

if (!Database::tableExists('sss_form_instances')) {
	Database::query("
		CREATE TABLE sss_form_instances (
			id bigint {$identity} primary key NOT NULL,
			student_id numeric NOT NULL,
			staff_id numeric NOT NULL,
			form_id bigint NOT NULL,
			saved {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			deleted_at {$timestamp},
			raw_data {$text},
			drafted {$timestamp},
			event_instance_id bigint
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_form_instances_id_seq');
		Database::query('ALTER SEQUENCE sss_form_instances_id_seq OWNED BY sss_form_instances.id');
		Database::query("ALTER TABLE sss_form_instances ALTER COLUMN id SET DEFAULT nextval('sss_form_instances_id_seq')");
	}
	Database::query("CREATE INDEX sss_form_instances_student_id_ind ON sss_form_instances (student_id)");
	Database::query("CREATE INDEX sss_form_instances_staff_id_ind ON sss_form_instances (staff_id)");
	Database::query("ALTER TABLE sss_form_instances ADD CONSTRAINT sss_form_instances_form_id_foreign FOREIGN KEY (form_id) REFERENCES sss_forms(id);");
	Database::query("ALTER TABLE sss_form_instances ADD CONSTRAINT sss_form_instances_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES users(staff_id);");
	Database::query("ALTER TABLE sss_form_instances ADD CONSTRAINT sss_form_instances_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id);");
}

if (!Database::tableExists('sss_form_collections')) {
	Database::query("
		CREATE TABLE sss_form_collections (
			id bigint {$identity} primary key NOT NULL,
			form_id bigint NOT NULL,
			name varchar(255) NOT NULL,
			sql {$text} NOT NULL,
			deleted_at {$timestamp}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_form_collections_id_seq');
		Database::query('ALTER SEQUENCE sss_form_collections_id_seq OWNED BY sss_form_collections.id');
		Database::query("ALTER TABLE sss_form_collections ALTER COLUMN id SET DEFAULT nextval('sss_form_collections_id_seq')");
	}
}

if (!Database::tableExists('sss_form_fields')) {
	Database::query("
		CREATE TABLE sss_form_fields (
			id bigint {$identity} primary key NOT NULL,
			form_id bigint NOT NULL,
			type varchar(255) NOT NULL,
			options {$text} NOT NULL,
			name varchar(255) NOT NULL,
			deleted_at {$timestamp},
			data_source_id bigint
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_form_fields_id_seq');
		Database::query('ALTER SEQUENCE sss_form_fields_id_seq OWNED BY sss_form_fields.id');
		Database::query("ALTER TABLE sss_form_fields ALTER COLUMN id SET DEFAULT nextval('sss_form_fields_id_seq')");
	}
	Database::query("CREATE INDEX sss_form_field_field_name_ind ON sss_form_fields (name);");
	Database::query("CREATE INDEX sss_form_fields_form_id_ind ON sss_form_fields (form_id);");
	Database::query("ALTER TABLE sss_form_fields ADD CONSTRAINT sss_form_fields_form_id_foreign FOREIGN KEY (form_id) REFERENCES sss_forms(id) ON DELETE CASCADE;");
}

if (!Database::tableExists('sss_form_field_instances')) {
	Database::query("
		CREATE TABLE sss_form_field_instances (
			id bigint {$identity} primary key NOT NULL,
			field_id bigint NOT NULL,
			form_instance_id bigint NOT NULL,
			value {$text},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			field_name varchar(255),
			data_id bigint,
			form_id bigint
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_form_field_instances_id_seq');
		Database::query('ALTER SEQUENCE sss_form_field_instances_id_seq OWNED BY sss_form_field_instances.id');
		Database::query("ALTER TABLE sss_form_field_instances ALTER COLUMN id SET DEFAULT nextval('sss_form_field_instances_id_seq')");
	}
	Database::query("CREATE INDEX sss_form_field_instances_field_id_ind ON sss_form_field_instances (field_id);");
	Database::query("CREATE INDEX sss_form_field_instances_field_name_ind ON sss_form_field_instances (field_name)");
	Database::query("CREATE INDEX sss_form_field_instances_form_instance_id_ind ON sss_form_field_instances (form_instance_id)");
	Database::query("ALTER TABLE sss_form_field_instances ADD CONSTRAINT sss_form_field_instances_field_id_foreign FOREIGN KEY (field_id) REFERENCES sss_form_fields(id);");
	Database::query("ALTER TABLE sss_form_field_instances ADD CONSTRAINT sss_form_field_instances_form_instance_id_foreign FOREIGN KEY (form_instance_id) REFERENCES sss_form_instances(id);");
}
