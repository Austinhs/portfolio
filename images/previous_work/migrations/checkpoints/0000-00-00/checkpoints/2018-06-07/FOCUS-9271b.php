<?php
// Tags: SSS, Formbuilder
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

if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::tableExists('sss_accommodation_categories')) {
	Database::query("
		CREATE TABLE sss_accommodation_categories (
			id bigint {$identity} primary key NOT NULL,
			title varchar(255) NOT NULL,
			sort_order integer NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_accommodation_categories_id_seq');
		Database::query("ALTER SEQUENCE sss_accommodation_categories_id_seq OWNED BY sss_accommodation_categories.id");
		Database::query("ALTER TABLE sss_accommodation_categories ALTER COLUMN id SET DEFAULT nextval('sss_accommodation_categories_id_seq')");
	}
}

if (!Database::tableExists('sss_accommodation_options')) {
	Database::query("
		CREATE TABLE sss_accommodation_options (
			id bigint {$identity} primary key NOT NULL,
			category_id bigint NOT NULL,
			long_desc {$text} NOT NULL,
			short_desc {$text}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_accommodation_options_id_seq');
		Database::query("ALTER SEQUENCE sss_accommodation_options_id_seq OWNED BY sss_accommodation_options.id");
		Database::query("ALTER TABLE sss_accommodation_options ALTER COLUMN id SET DEFAULT nextval('sss_accommodation_options_id_seq')");
	}
	Database::query("ALTER TABLE sss_accommodation_options ADD CONSTRAINT sss_accommodation_options_category_id_foreign FOREIGN KEY (category_id) REFERENCES sss_accommodation_categories(id);");
}

if (!Database::tableExists('sss_accommodations')) {
	Database::query("
		CREATE TABLE sss_accommodations (
			id bigint {$identity} primary key NOT NULL,
			student_id numeric NOT NULL,
			duration varchar(255),
			deleted_at {$timestamp},
			event_instance_id bigint DEFAULT (0) NOT NULL,
			accommodation_option_id bigint,
			description {$text},
			location {$text},
			frequency {$text}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_accommodations_id_seq');
		Database::query("ALTER SEQUENCE sss_accommodations_id_seq OWNED BY sss_accommodations.id");
		Database::query("ALTER TABLE sss_accommodations ALTER COLUMN id SET DEFAULT nextval('sss_accommodations_id_seq')");
	}
	Database::query("ALTER TABLE sss_accommodations ADD CONSTRAINT sss_accommodations_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id);");
}

if (!Database::tableExists('sss_alerts')) {
	Database::query("
		CREATE TABLE sss_alerts (
			id bigint {$identity} primary key NOT NULL,
			message {$text} NOT NULL,
			sender_id numeric NOT NULL,
			recipient_id numeric,
			profile_id numeric,
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_alerts_id_seq');
		Database::query("ALTER SEQUENCE sss_alerts_id_seq OWNED BY sss_alerts.id");
		Database::query("ALTER TABLE sss_alerts ALTER COLUMN id SET DEFAULT nextval('sss_alerts_id_seq')");
	}
	Database::query("ALTER TABLE sss_alerts ADD CONSTRAINT sss_alerts_profile_id_foreign FOREIGN KEY (profile_id) REFERENCES user_profiles(id);");
	Database::query("ALTER TABLE sss_alerts ADD CONSTRAINT sss_alerts_recipient_id_foreign FOREIGN KEY (recipient_id) REFERENCES users(staff_id);");
	Database::query("ALTER TABLE sss_alerts ADD CONSTRAINT sss_alerts_sender_id_foreign FOREIGN KEY (sender_id) REFERENCES users(staff_id);");
}

if (!Database::tableExists('sss_archives')) {
	Database::query("
		CREATE TABLE sss_archives (
			id bigint {$identity} primary key NOT NULL,
			student_id numeric NOT NULL,
			relative_path varchar(255) NOT NULL,
			base_path varchar(255) NOT NULL,
			event_date date,
			name varchar(255) NOT NULL,
			size varchar(255) NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_archives_id_seq');
		Database::query('ALTER SEQUENCE sss_archives_id_seq OWNED BY sss_archives.id');
		Database::query("ALTER TABLE sss_archives ALTER COLUMN id SET DEFAULT nextval('sss_archives_id_seq')");
	}
	Database::query("ALTER TABLE sss_archives ADD CONSTRAINT sss_archives_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id);");
}

if (!Database::tableExists('sss_caseload')) {
	Database::query("
		CREATE TABLE sss_caseload (
			student_id numeric NOT NULL,
			staff_id bigint NOT NULL,
			accepted {$boolean} DEFAULT {$false} NOT NULL,
			created_at date NOT NULL
		)
	");
}

if (!Database::tableExists('sss_caseload_transfers')) {
	Database::query("
		CREATE TABLE sss_caseload_transfers (
			sender_staff_id bigint NOT NULL,
			reciever_staff_id bigint NOT NULL,
			student_id numeric NOT NULL
		)
	");
}

if (!Database::tableExists('sss_config')) {
	Database::query("
		CREATE TABLE sss_config (
			id bigint {$identity} primary key NOT NULL,
			last_changed_user numeric,
			value {$text},
			name {$text} NOT NULL,
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_config_id_seq');
		Database::query('ALTER SEQUENCE sss_config_id_seq OWNED BY sss_config.id');
		Database::query("ALTER TABLE sss_config ALTER COLUMN id SET DEFAULT nextval('sss_config_id_seq')");
	}
	Database::query("ALTER TABLE sss_config ADD CONSTRAINT sss_config_last_changed_user_foreign FOREIGN KEY (last_changed_user) REFERENCES users(staff_id);");
}

if (!Database::tableExists('sss_data')) {
	$table = keyword('table');
	$key   = keyword('key');
	$value = keyword('value');

	Database::query("
		CREATE TABLE sss_data (
			id bigint {$identity} primary key NOT NULL,
			{$table} {$text} NOT NULL,
			table_id bigint NOT NULL,
			{$key} varchar(255) NOT NULL,
			{$value} {$text},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			type varchar(255) DEFAULT 'text',
			description {$text}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_data_id_seq');
		Database::query('ALTER SEQUENCE sss_data_id_seq OWNED BY sss_data.id');
		Database::query("ALTER TABLE sss_data ALTER COLUMN id SET DEFAULT nextval('sss_data_id_seq')");
	}
	Database::query("CREATE INDEX sss_data_table_id_ind ON sss_data(table_id)");
}

if (!Database::tableExists('sss_domains')) {
	Database::query("
		CREATE TABLE sss_domains (
			id bigint {$identity} primary key NOT NULL,
			name varchar(255) NOT NULL,
			deleted_at {$timestamp}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_domains_id_seq');
		Database::query('ALTER SEQUENCE sss_domains_id_seq OWNED BY sss_domains.id');
		Database::query("ALTER TABLE sss_domains ALTER COLUMN id SET DEFAULT nextval('sss_domains_id_seq')");
	}
}

if (!Database::tableExists('sss_events')) {
	Database::query("
		CREATE TABLE sss_events (
			id bigint {$identity} primary key NOT NULL,
			name {$text} NOT NULL,
			category {$text},
			handler {$text},
			description {$text},
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			max_concurrent bigint,
			max_total bigint,
			dependencies {$text},
			groups {$text},
			disabled {$boolean}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_events_id_seq');
		Database::query('ALTER SEQUENCE sss_events_id_seq OWNED BY sss_events.id');
		Database::query("ALTER TABLE sss_events ALTER COLUMN id SET DEFAULT nextval('sss_events_id_seq')");
	}
}

if (!Database::tableExists('sss_event_instances')) {
	Database::query("
		CREATE TABLE sss_event_instances (
			id bigint {$identity} primary key NOT NULL,
			event_id bigint NOT NULL,
			student_id numeric NOT NULL,
			staff_id bigint NOT NULL,
			instance_id bigint,
			status varchar(255),
			due {$timestamp},
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			scheduled {$timestamp},
			initiated {$timestamp},
			uploads {$boolean} DEFAULT {$true} NOT NULL,
			locked {$timestamp},
			program varchar(255),
			campus numeric,
			noforms {$boolean} DEFAULT {$true} NOT NULL,
			parent_event_id bigint,
			inactive {$boolean} DEFAULT {$false} NOT NULL,
			reason {$text},
			locked_by bigint
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_event_instances_id_seq');
		Database::query('ALTER SEQUENCE sss_event_instances_id_seq OWNED BY sss_event_instances.id');
		Database::query("ALTER TABLE sss_event_instances ALTER COLUMN id SET DEFAULT nextval('sss_event_instances_id_seq')");
	}
	Database::query("CREATE INDEX sss_event_instances_event_id_ind ON sss_event_instances (event_id)");
	Database::query("CREATE INDEX sss_event_instances_parent_event_id_ind ON sss_event_instances (parent_event_id)");
	Database::query("CREATE INDEX sss_event_instances_status_ind ON sss_event_instances (status)");
	Database::query("CREATE INDEX sss_event_instances_student_id_ind ON sss_event_instances (student_id)");
	Database::query("ALTER TABLE sss_event_instances ADD CONSTRAINT sss_event_instances_event_id_foreign FOREIGN KEY (event_id) REFERENCES sss_events(id);");
	Database::query("ALTER TABLE sss_event_instances ADD CONSTRAINT sss_event_instances_parent_event_id_foreign FOREIGN KEY (parent_event_id) REFERENCES sss_event_instances(id);");
	Database::query("ALTER TABLE sss_event_instances ADD CONSTRAINT sss_event_instances_student_id_fkey FOREIGN KEY (student_id) REFERENCES students(student_id);");

	if (!Database::columnExists('sss_form_instances', 'event_instance_id')) {
		Database::createColumn('sss_form_instances', 'event_instance_id', 'bigint');
	}

	Database::query("ALTER TABLE sss_form_instances ADD CONSTRAINT sss_form_instances_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
} else {
	$type = "";
	if (Database::$type === "postgres") {
		$type = "TYPE";
	}

	Database::query("ALTER TABLE sss_event_instances ALTER COLUMN status {$type} VARCHAR(255)");
}

if (!Database::tableExists('sss_evaluations')) {
	Database::query("
		CREATE TABLE sss_evaluations (
			id bigint {$identity} primary key NOT NULL,
			student_id numeric NOT NULL,
			staff_id numeric NOT NULL,
			status varchar(255) NOT NULL,
			request_sent {$timestamp} NOT NULL,
			consent_received {$timestamp},
			completed {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			deleted_at {$timestamp},
			event_instance_id bigint
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_evaluations_id_seq');
		Database::query('ALTER SEQUENCE sss_evaluations_id_seq OWNED BY sss_evaluations.id');
		Database::query("ALTER TABLE sss_evaluations ALTER COLUMN id SET DEFAULT nextval('sss_evaluations_id_seq')");
	}
	Database::query("ALTER TABLE sss_evaluations ADD CONSTRAINT sss_evaluations_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
	Database::query("ALTER TABLE sss_evaluations ADD CONSTRAINT sss_evaluations_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES users(staff_id);");
	Database::query("ALTER TABLE sss_evaluations ADD CONSTRAINT sss_evaluations_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id);");
}

if (!Database::tableExists('sss_evaluation_types')) {
	Database::query("
		CREATE TABLE sss_evaluation_types (
			id bigint {$identity} primary key NOT NULL,
			type varchar(255) NOT NULL,
			severity varchar(255) NOT NULL,
			disabled {$boolean},
			name varchar(255) NOT NULL,
			form bigint,
			field {$text}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_evaluation_types_id_seq');
		Database::query('ALTER SEQUENCE sss_evaluation_types_id_seq OWNED BY sss_evaluation_types.id');
		Database::query("ALTER TABLE sss_evaluation_types ALTER COLUMN id SET DEFAULT nextval('sss_evaluation_types_id_seq')");
	}
}

if (!Database::tableExists('sss_evaluation_form_instances')) {
	Database::query("
		CREATE TABLE sss_evaluation_form_instances (
			id bigint {$identity} primary key NOT NULL,
			evaluation_id bigint NOT NULL,
			form_instance_id bigint NOT NULL,
			evaluation_type_id bigint NOT NULL,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_evaluation_form_instances_id_seq');
		Database::query('ALTER SEQUENCE sss_evaluation_form_instances_id_seq OWNED BY sss_evaluation_form_instances.id');
		Database::query("ALTER TABLE sss_evaluation_form_instances ALTER COLUMN id SET DEFAULT nextval('sss_evaluation_form_instances_id_seq')");
	}
	Database::query("ALTER TABLE sss_evaluation_form_instances ADD CONSTRAINT sss_evaluation_form_instances_evaluation_id_foreign FOREIGN KEY (evaluation_id) REFERENCES sss_evaluations(id);");
	Database::query("ALTER TABLE sss_evaluation_form_instances ADD CONSTRAINT sss_evaluation_form_instances_evaluation_type_id_foreign FOREIGN KEY (evaluation_type_id) REFERENCES sss_evaluation_types(id);");
	Database::query("ALTER TABLE sss_evaluation_form_instances ADD CONSTRAINT sss_evaluation_form_instances_form_instance_id_foreign FOREIGN KEY (form_instance_id) REFERENCES sss_form_instances(id);");
}

if (!Database::tableExists('sss_event_logs')) {
	Database::query("
		CREATE TABLE sss_event_logs (
			id bigint {$identity} primary key NOT NULL,
			event_instance_id bigint,
			student_id numeric NOT NULL,
			log_entry_id bigint,
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			log_complete {$boolean} DEFAULT {$false} NOT NULL,
			event_complete {$boolean} DEFAULT {$false} NOT NULL,
			hold_data {$text},
			time_completed {$timestamp}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_event_logs_id_seq');
		Database::query('ALTER SEQUENCE sss_event_logs_id_seq OWNED BY sss_event_logs.id');
		Database::query("ALTER TABLE sss_event_logs ALTER COLUMN id SET DEFAULT nextval('sss_event_logs_id_seq')");
	}
	Database::query("ALTER TABLE sss_event_logs ADD CONSTRAINT sss_event_logs_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
	Database::query("ALTER TABLE sss_event_logs ADD CONSTRAINT sss_event_logs_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id);");
}

if (!Database::tableExists('sss_event_permissions')) {
	$delete = keyword('delete');
	$view   = keyword('view');

	Database::query("
		CREATE TABLE sss_event_permissions (
			id bigint {$identity} primary key NOT NULL,
			event_id bigint NOT NULL,
			profile_id numeric NOT NULL,
			{$view} {$boolean},
			edit {$boolean},
			{$delete} {$boolean},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			deleted_at {$timestamp},
			lock {$boolean} DEFAULT {$false} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_event_permissions_id_seq');
		Database::query('ALTER SEQUENCE sss_event_permissions_id_seq OWNED BY sss_event_permissions.id');
		Database::query("ALTER TABLE sss_event_permissions ALTER COLUMN id SET DEFAULT nextval('sss_event_permissions_id_seq')");
	}
	Database::query("CREATE INDEX sss_event_permissions_event_id_ind ON sss_event_permissions (event_id)");
	Database::query("CREATE INDEX sss_event_permissions_profile_id_ind ON sss_event_permissions (profile_id);");
	Database::query("ALTER TABLE sss_event_permissions ADD CONSTRAINT sss_event_permissions_event_id_foreign FOREIGN KEY (event_id) REFERENCES sss_events(id);");
	Database::query("ALTER TABLE sss_event_permissions ADD CONSTRAINT sss_event_permissions_profile_id_foreign FOREIGN KEY (profile_id) REFERENCES user_profiles(id);");
}

if (!Database::tableExists('sss_event_steps')) {
	Database::query("
		CREATE TABLE sss_event_steps (
			id bigint {$identity} primary key NOT NULL,
			name {$text} NOT NULL,
			handler {$text} NOT NULL,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			classname {$text},
			reload {$boolean} DEFAULT {$false} NOT NULL,
			permissions {$text},
			subtitle varchar(255),
			hide_on_step_list {$boolean},
			hide_on_print_list {$boolean} DEFAULT {$false} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_event_steps_id_seq');
		Database::query('ALTER SEQUENCE sss_event_steps_id_seq OWNED BY sss_event_steps.id');
		Database::query("ALTER TABLE sss_event_steps ALTER COLUMN id SET DEFAULT nextval('sss_event_steps_id_seq')");
	}
}


if (!Database::tableExists('sss_event_step_instances')) {
	Database::query("
		CREATE TABLE sss_event_step_instances (
			id bigint {$identity} primary key NOT NULL,
			event_step_id bigint NOT NULL,
			event_instance_id bigint NOT NULL,
			status {$text} NOT NULL,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			required {$boolean} DEFAULT {$false} NOT NULL,
			sort_order bigint DEFAULT (1) NOT NULL,
			alt_title varchar(255),
			alt_subtitle varchar(255),
			cloneable {$boolean} DEFAULT {$true} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_event_step_instances_id_seq');
		Database::query('ALTER SEQUENCE sss_event_step_instances_id_seq OWNED BY sss_event_step_instances.id');
		Database::query("ALTER TABLE sss_event_step_instances ALTER COLUMN id SET DEFAULT nextval('sss_event_step_instances_id_seq')");
	}
	Database::query("CREATE INDEX sss_event_step_instances_event_instance_id_ind ON sss_event_step_instances (event_instance_id);");
	Database::query("CREATE INDEX sss_event_step_instances_step_id_ind ON sss_event_step_instances (event_step_id);");
	Database::query("ALTER TABLE sss_event_step_instances ADD CONSTRAINT sss_event_step_instances_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
	Database::query("ALTER TABLE sss_event_step_instances ADD CONSTRAINT sss_event_step_instances_event_step_id_foreign FOREIGN KEY (event_step_id) REFERENCES sss_event_steps(id);");
}

if (!Database::tableExists('sss_event_step_instance_forms')) {
	Database::query("
		CREATE TABLE sss_event_step_instance_forms (
			id bigint {$identity} primary key NOT NULL,
			form_instance_id bigint NOT NULL,
			step_instance_id bigint NOT NULL,
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			deleted_at {$timestamp}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_event_step_instance_forms_id_seq');
		Database::query('ALTER SEQUENCE sss_event_step_instance_forms_id_seq OWNED BY sss_event_step_instance_forms.id');
		Database::query("ALTER TABLE sss_event_step_instance_forms ALTER COLUMN id SET DEFAULT nextval('sss_event_step_instance_forms_id_seq')");
	}
	Database::query("CREATE INDEX sss_event_step_instance_forms_form_instance_id_ind ON sss_event_step_instance_forms (form_instance_id);");
	Database::query("CREATE INDEX sss_event_step_instance_forms_step_instance_id_ind ON sss_event_step_instance_forms (step_instance_id);");
	Database::query("ALTER TABLE sss_event_step_instance_forms ADD CONSTRAINT sss_event_step_instance_forms_form_instance_id_foreign FOREIGN KEY (form_instance_id) REFERENCES sss_form_instances(id);");
	Database::query("ALTER TABLE sss_event_step_instance_forms ADD CONSTRAINT sss_event_step_instance_forms_step_instance_id_foreign FOREIGN KEY (step_instance_id) REFERENCES sss_event_step_instances(id);");
}

if (!Database::tableExists('sss_event_step_sequences')) {
	Database::query("
		CREATE TABLE sss_event_step_sequences (
			id bigint {$identity} primary key NOT NULL,
			event_step_id bigint NOT NULL,
			event_id bigint NOT NULL,
			seq bigint NOT NULL,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			required {$boolean} DEFAULT {$false} NOT NULL,
			alt_title varchar(255),
			alt_subtitle varchar(255),
			cloneable {$boolean} DEFAULT {$true} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_event_step_sequences_id_seq');
		Database::query('ALTER SEQUENCE sss_event_step_sequences_id_seq OWNED BY sss_event_step_sequences.id');
		Database::query("ALTER TABLE sss_event_step_sequences ALTER COLUMN id SET DEFAULT nextval('sss_event_step_sequences_id_seq')");
	}
	Database::query("CREATE INDEX sss_event_step_sequences_event_id_ind ON sss_event_step_sequences (event_id);");
	Database::query("CREATE INDEX sss_event_step_sequences_step_id_ind ON sss_event_step_sequences (event_step_id);");
	Database::query("ALTER TABLE sss_event_step_sequences ADD CONSTRAINT sss_event_step_sequences_event_id_foreign FOREIGN KEY (event_id) REFERENCES sss_events(id);");
	Database::query("ALTER TABLE sss_event_step_sequences ADD CONSTRAINT sss_event_step_sequences_event_step_id_foreign FOREIGN KEY (event_step_id) REFERENCES sss_event_steps(id);");
}

if (!Database::tableExists('sss_tags')) {
	Database::query("
		CREATE TABLE sss_tags (
			id bigint {$identity} primary key NOT NULL,
			name varchar(255) NOT NULL,
			title varchar(255)
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_tags_id_seq');
		Database::query('ALTER SEQUENCE sss_tags_id_seq OWNED BY sss_tags.id');
		Database::query("ALTER TABLE sss_tags ALTER COLUMN id SET DEFAULT nextval('sss_tags_id_seq')");
	}
}

if (!Database::tableExists('sss_event_tags')) {
	Database::query("
		CREATE TABLE sss_event_tags (
			id bigint {$identity} primary key NOT NULL,
			event_id bigint NOT NULL,
			tag_id bigint NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_event_tags_id_seq');
		Database::query('ALTER SEQUENCE sss_event_tags_id_seq OWNED BY sss_event_tags.id');
		Database::query("ALTER TABLE sss_event_tags ALTER COLUMN id SET DEFAULT nextval('sss_event_tags_id_seq')");
	}
	Database::query("ALTER TABLE sss_event_tags ADD CONSTRAINT sss_event_tags_event_id_foreign FOREIGN KEY (event_id) REFERENCES sss_events(id);");
	Database::query("ALTER TABLE sss_event_tags ADD CONSTRAINT sss_event_tags_tag_id_foreign FOREIGN KEY (tag_id) REFERENCES sss_tags(id);");
}

if (!Database::tableExists('sss_event_triggers')) {
	Database::query("
		CREATE TABLE sss_event_triggers (
			id bigint {$identity} primary key NOT NULL,
			event_id bigint NOT NULL,
			condition {$text} NOT NULL,
			action {$text} NOT NULL,
			delay {$text},
			params {$text},
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			secondary_condition {$text},
			sort_order bigint DEFAULT (0) NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_triggers_id_seq');
		Database::query('ALTER SEQUENCE sss_triggers_id_seq OWNED BY sss_event_triggers.id');
		Database::query("ALTER TABLE sss_event_triggers ALTER COLUMN id SET DEFAULT nextval('sss_triggers_id_seq')");
	}
}

if (!Database::tableExists('sss_fie_evaluators')) {
	Database::query("
		CREATE TABLE sss_fie_evaluators (
			id bigint {$identity} primary key NOT NULL,
			staff_id numeric NOT NULL,
			fie_id bigint NOT NULL,
			lead {$boolean} DEFAULT {$false} NOT NULL,
			evaluation_types {$text} NOT NULL,
			finished {$boolean} DEFAULT {$false} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_fie_evaluators_id_seq');
		Database::query('ALTER SEQUENCE sss_fie_evaluators_id_seq OWNED BY sss_fie_evaluators.id');
		Database::query("ALTER TABLE sss_fie_evaluators ALTER COLUMN id SET DEFAULT nextval('sss_fie_evaluators_id_seq')");
	}
	Database::query("ALTER TABLE sss_fie_evaluators ADD CONSTRAINT sss_fie_evaluators_fie_id_staff_id_unique UNIQUE (fie_id, staff_id)");
	Database::query("ALTER TABLE sss_fie_evaluators ADD CONSTRAINT sss_fie_evaluators_fie_id_foreign FOREIGN KEY (fie_id) REFERENCES sss_event_instances(id);");
	Database::query("ALTER TABLE sss_fie_evaluators ADD CONSTRAINT sss_fie_evaluators_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES users(staff_id)");
}

if (!Database::tableExists('sss_form_bindings')) {
	Database::query("
		CREATE TABLE sss_form_bindings (
			id bigint {$identity} primary key NOT NULL,
			form_id bigint NOT NULL,
			required {$boolean} DEFAULT {$false} NOT NULL,
			editable {$boolean} DEFAULT {$false} NOT NULL,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			table_id bigint,
			table_name varchar(255)
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_form_bindings_id_seq');
		Database::query('ALTER SEQUENCE sss_form_bindings_id_seq OWNED BY sss_form_bindings.id');
		Database::query("ALTER TABLE sss_form_bindings ALTER COLUMN id SET DEFAULT nextval('sss_form_bindings_id_seq')");
	}
	Database::query("CREATE INDEX sss_form_bindings_form_id_ind ON sss_form_bindings (form_id)");
	Database::query("ALTER TABLE sss_form_bindings ADD CONSTRAINT sss_form_bindings_form_id_foreign FOREIGN KEY (form_id) REFERENCES sss_forms(id);");
}

if (!Database::tableExists('sss_form_triggers')) {
	Database::query("
		CREATE TABLE sss_form_triggers (
			id bigint {$identity} primary key NOT NULL,
			form_id bigint NOT NULL,
			form_field_id bigint,
			action {$text} NOT NULL,
			condition {$text},
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			params {$text},
			field_name varchar(255),
			sort_order bigint DEFAULT (0) NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_form_triggers_id_seq');
		Database::query('ALTER SEQUENCE sss_form_triggers_id_seq OWNED BY sss_form_triggers.id');
		Database::query("ALTER TABLE sss_form_triggers ALTER COLUMN id SET DEFAULT nextval('sss_form_triggers_id_seq')");
	}
}

if (!Database::tableExists('sss_goals')) {
	Database::query("
		CREATE TABLE sss_goals (
			id bigint {$identity} primary key NOT NULL,
			student_id numeric NOT NULL,
			timeframe varchar(255) NOT NULL,
			condition varchar(255) NOT NULL,
			behavior varchar(255) NOT NULL,
			criterion varchar(255) NOT NULL,
			responsible_implementer varchar(255) NOT NULL,
			service_type varchar(255) NOT NULL,
			started_at date,
			ended_at date,
			domain_id bigint NOT NULL,
			event_instance_id bigint,
			domain_other varchar(255)
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_goals_id_seq');
		Database::query('ALTER SEQUENCE sss_goals_id_seq OWNED BY sss_goals.id');
		Database::query("ALTER TABLE sss_goals ALTER COLUMN id SET DEFAULT nextval('sss_goals_id_seq')");
	}
	Database::query("ALTER TABLE sss_goals ADD CONSTRAINT sss_goals_domain_id_foreign FOREIGN KEY (domain_id) REFERENCES sss_domains(id) ON DELETE CASCADE;");
	Database::query("ALTER TABLE sss_goals ADD CONSTRAINT sss_goals_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
	Database::query("ALTER TABLE sss_goals ADD CONSTRAINT sss_goals_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id);");
}

if (!Database::tableExists('sss_instructional_services')) {
	Database::query("
		CREATE TABLE sss_instructional_services (
			id bigint {$identity} primary key NOT NULL,
			subject_area varchar(255) NOT NULL,
			modified_instruction {$boolean} NOT NULL
		)
	");

}

if (!Database::tableExists('sss_interpreters')) {
	Database::query("
		CREATE TABLE sss_interpreters (
			id bigint {$identity} primary key NOT NULL,
			name {$text} NOT NULL,
			modes {$text} NOT NULL,
			agreement {$timestamp} NOT NULL,
			status {$text} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_interpreters_id_seq');
		Database::query('ALTER SEQUENCE sss_interpreters_id_seq OWNED BY sss_interpreters.id');
		Database::query("ALTER TABLE sss_interpreters ALTER COLUMN id SET DEFAULT nextval('sss_interpreters_id_seq')");
	}
}

if (!Database::tableExists('sss_languages')) {
	Database::query("
		CREATE TABLE sss_languages (
			id bigint {$identity} primary key NOT NULL,
			language varchar(255) NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_languages_id_seq');
		Database::query('ALTER SEQUENCE sss_languages_id_seq OWNED BY sss_languages.id');
		Database::query("ALTER TABLE sss_languages ALTER COLUMN id SET DEFAULT nextval('sss_languages_id_seq')");
	}
}

if (!Database::tableExists('sss_logging_field_options')) {
	Database::query("
		CREATE TABLE sss_logging_field_options (
			id bigint {$identity} primary key NOT NULL,
			field_id bigint NOT NULL,
			log_field_id bigint NOT NULL,
			required {$boolean},
			editable {$boolean},
			initialization {$text},
			sort_order integer,
			schedule_date {$boolean} DEFAULT {$false} NOT NULL,
			validation_code {$text},
			initialization_code {$text}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_logging_field_options_id_seq');
		Database::query('ALTER SEQUENCE sss_logging_field_options_id_seq OWNED BY sss_logging_field_options.id');
		Database::query("ALTER TABLE sss_logging_field_options ALTER COLUMN id SET DEFAULT nextval('sss_logging_field_options_id_seq')");
	}
}

if (!Database::tableExists('sss_meeting_minutes')) {
	Database::query("
		CREATE TABLE sss_meeting_minutes (
			id bigint {$identity} primary key NOT NULL,
			event_instance_id bigint NOT NULL,
			date date NOT NULL,
			meeting_type varchar(255) DEFAULT 'initial' NOT NULL,
			minutes {$text} DEFAULT '' NOT NULL,
			notetaker varchar(255) DEFAULT 'Heaven Espinoza' NOT NULL,
			step_instance_id bigint
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_meeting_minutes_id_seq');
		Database::query('ALTER SEQUENCE sss_meeting_minutes_id_seq OWNED BY sss_meeting_minutes.id');
		Database::query("ALTER TABLE sss_meeting_minutes ALTER COLUMN id SET DEFAULT nextval('sss_meeting_minutes_id_seq')");
	}
	Database::query("ALTER TABLE sss_meeting_minutes ADD CONSTRAINT sss_meeting_minutes_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
}

if (!Database::tableExists('sss_notes')) {
	$table = keyword('table');
	Database::query("
		CREATE TABLE sss_notes (
			id bigint {$identity} primary key NOT NULL,
			notes {$text},
			user_id numeric,
			last_changed_user numeric NOT NULL,
			{$table} {$text} NOT NULL,
			table_id bigint NOT NULL,
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_notes_id_seq');
		Database::query('ALTER SEQUENCE sss_notes_id_seq OWNED BY sss_notes.id');
		Database::query("ALTER TABLE sss_notes ALTER COLUMN id SET DEFAULT nextval('sss_notes_id_seq')");
	}
	Database::query("ALTER TABLE sss_notes ADD CONSTRAINT sss_notes_last_changed_user_foreign FOREIGN KEY (last_changed_user) REFERENCES users(staff_id)");
	Database::query("ALTER TABLE sss_notes ADD CONSTRAINT sss_notes_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(staff_id);");
}

if (!Database::tableExists('sss_objectives')) {
	Database::query("
		CREATE TABLE sss_objectives (
			id bigint {$identity} primary key NOT NULL,
			goal_id bigint NOT NULL,
			timeframe varchar(255) NOT NULL,
			condition varchar(255) NOT NULL,
			behavior varchar(255) NOT NULL,
			criterion varchar(255) NOT NULL,
			percent_mastered integer
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_objectives_id_seq');
		Database::query('ALTER SEQUENCE sss_objectives_id_seq OWNED BY sss_objectives.id');
		Database::query("ALTER TABLE sss_objectives ALTER COLUMN id SET DEFAULT nextval('sss_objectives_id_seq')");
	}
	Database::query("ALTER TABLE sss_objectives ADD CONSTRAINT sss_objectives_goal_id_foreign FOREIGN KEY (goal_id) REFERENCES sss_goals(id) ON DELETE CASCADE;");
}

if (!Database::tableExists('sss_objective_procedure_codes')) {
	Database::query("
		CREATE TABLE sss_objective_procedure_codes (
			id bigint {$identity} primary key NOT NULL,
			code varchar(255) NOT NULL,
			text varchar(255) NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_objective_procedure_codes_id_seq');
		Database::query('ALTER SEQUENCE sss_objective_procedure_codes_id_seq OWNED BY sss_objective_procedure_codes.id');
		Database::query("ALTER TABLE sss_objective_procedure_codes ALTER COLUMN id SET DEFAULT nextval('sss_objective_procedure_codes_id_seq')");
	}
}

if (!Database::tableExists('sss_objective_join_procedures')) {
	Database::query("
		CREATE TABLE sss_objective_join_procedures (
			objective_id bigint NOT NULL,
			procedure_id bigint NOT NULL
		)
	");

	Database::query("ALTER TABLE sss_objective_join_procedures ADD CONSTRAINT sss_objective_join_procedures_objective_id_foreign FOREIGN KEY (objective_id) REFERENCES sss_objectives(id) ON DELETE CASCADE;");
	Database::query("ALTER TABLE sss_objective_join_procedures ADD CONSTRAINT sss_objective_join_procedures_procedure_id_foreign FOREIGN KEY (procedure_id) REFERENCES sss_objective_procedure_codes(id) ON DELETE CASCADE;");
}

if (!Database::tableExists('sss_permissions')) {
	Database::query("
		CREATE TABLE sss_permissions (
			id bigint {$identity} primary key NOT NULL,
			name {$text} NOT NULL,
			description {$text} NOT NULL,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			category {$text},
			short_name {$text}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_permissions_id_seq');
		Database::query('ALTER SEQUENCE sss_permissions_id_seq OWNED BY sss_permissions.id');
		Database::query("ALTER TABLE sss_permissions ALTER COLUMN id SET DEFAULT nextval('sss_permissions_id_seq')");
	}
}

if (!Database::tableExists('sss_profile_permissions')) {
	Database::query("
		CREATE TABLE sss_profile_permissions (
			id bigint {$identity} primary key NOT NULL,
			profile_id bigint NOT NULL,
			permission_id bigint NOT NULL,
			permission {$text} NOT NULL,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_profile_permissions_id_seq');
		Database::query('ALTER SEQUENCE sss_profile_permissions_id_seq OWNED BY sss_profile_permissions.id');
		Database::query("ALTER TABLE sss_profile_permissions ALTER COLUMN id SET DEFAULT nextval('sss_profile_permissions_id_seq')");
	}
	Database::query("CREATE INDEX sss_profile_permissions_profile_id_ind ON sss_profile_permissions(profile_id)");
	Database::query("ALTER TABLE sss_profile_permissions ADD CONSTRAINT sss_profile_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES sss_permissions(id);");
}

if (!Database::tableExists('sss_programs')) {
	Database::query("
		CREATE TABLE sss_programs (
			id bigint {$identity} primary key NOT NULL,
			name varchar(255) NOT NULL,
			short_name varchar(255) NOT NULL,
			color1 varchar(255) NOT NULL,
			color2 varchar(255) NOT NULL,
			sort_order bigint,
			deleted_at {$timestamp},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			manage_student {$boolean},
			custom_field_id bigint,
			main_menu {$boolean} DEFAULT {$false} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_programs_id_seq');
		Database::query('ALTER SEQUENCE sss_programs_id_seq OWNED BY sss_programs.id');
		Database::query("ALTER TABLE sss_programs ALTER COLUMN id SET DEFAULT nextval('sss_programs_id_seq')");
	}
}

if (!Database::tableExists('sss_progress_types')) {
	Database::query("
		CREATE TABLE sss_progress_types (
			id bigint {$identity} primary key NOT NULL,
			code varchar(255) NOT NULL,
			title varchar(255) NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_progress_types_id_seq');
		Database::query('ALTER SEQUENCE sss_progress_types_id_seq OWNED BY sss_progress_types.id');
		Database::query("ALTER TABLE sss_progress_types ALTER COLUMN id SET DEFAULT nextval('sss_progress_types_id_seq')");
	}
}

if (!Database::tableExists('sss_progress')) {
	Database::query("
		CREATE TABLE sss_progress (
			evaluated_at date NOT NULL,
			objective_id bigint,
			progress_type bigint NOT NULL,
			percent_mastered integer NOT NULL,
			description varchar(255) NOT NULL
		)
	");

	Database::query("ALTER TABLE sss_progress ADD CONSTRAINT sss_progress_objective_id_foreign FOREIGN KEY (objective_id) REFERENCES sss_objectives(id);");
	Database::query("ALTER TABLE sss_progress ADD CONSTRAINT sss_progress_progress_type_foreign FOREIGN KEY (progress_type) REFERENCES sss_progress_types(id);");
}

if (!Database::tableExists('sss_related_services')) {
	Database::query("
		CREATE TABLE sss_related_services (
			service_type varchar(255) NOT NULL,
			consult varchar(255) NOT NULL
		)
	");
}

if (!Database::tableExists('sss_reports')) {
	Database::query("
		CREATE TABLE sss_reports (
			id bigint {$identity} primary key NOT NULL,
			name varchar(255) NOT NULL,
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			deleted_at {$timestamp},
			description {$text},
			programs varchar(255),
			location varchar(255),
			base_table varchar(255) NOT NULL,
			joined_tables {$text},
			flags varchar(255),
			query {$text},
			builder_data {$text},
			cached_result {$text},
			cache_date {$timestamp},
			cache_lifetime bigint DEFAULT (86400),
			last_run {$timestamp},
			last_changed_user numeric NOT NULL,
			last_run_user numeric,
			manual {$boolean} DEFAULT {$false} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_reports_id_seq');
		Database::query('ALTER SEQUENCE sss_reports_id_seq OWNED BY sss_reports.id');
		Database::query("ALTER TABLE sss_reports ALTER COLUMN id SET DEFAULT nextval('sss_reports_id_seq')");
	}
	Database::query("ALTER TABLE sss_reports ADD CONSTRAINT sss_reports_last_changed_user_foreign FOREIGN KEY (last_changed_user) REFERENCES users(staff_id)");
	Database::query("ALTER TABLE sss_reports ADD CONSTRAINT sss_reports_last_run_user_foreign FOREIGN KEY (last_run_user) REFERENCES users(staff_id);");
}

if (!Database::tableExists('sss_reserve')) {
	Database::query("
		CREATE TABLE sss_reserve (
			student_id numeric NOT NULL,
			hold_date date NOT NULL,
			sped {$boolean} DEFAULT {$false} NOT NULL,
			section_504 {$boolean} DEFAULT {$false} NOT NULL,
			rti {$boolean} DEFAULT {$false} NOT NULL
		)
	");
}

if (!Database::tableExists('sss_resource_locks')) {
	Database::query("
		CREATE TABLE sss_resource_locks (
			id bigint {$identity} primary key NOT NULL,
			record_id bigint NOT NULL,
			staff_id numeric NOT NULL,
			record_table {$text} NOT NULL,
			initiated_time {$timestamp} NOT NULL,
			expiration_time {$timestamp} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_resource_locks_id_seq');
		Database::query('ALTER SEQUENCE sss_resource_locks_id_seq OWNED BY sss_resource_locks.id');
		Database::query("ALTER TABLE sss_resource_locks ALTER COLUMN id SET DEFAULT nextval('sss_resource_locks_id_seq')");
	}
	Database::query("CREATE INDEX sss_resource_locks_staff_id_ind ON sss_resource_locks (staff_id)");
	Database::query("ALTER TABLE sss_resource_locks ADD CONSTRAINT sss_resource_locks_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES users(staff_id)");
}

if (!Database::tableExists('sss_schedules')) {
	Database::query("
		CREATE TABLE sss_schedules (
			id bigint {$identity} primary key NOT NULL,
			start_date date NOT NULL,
			end_date date NOT NULL,
			event_instance_id bigint NOT NULL,
			ppcd_code varchar(255),
			override_minutes integer,
			rdspd_code varchar(255),
			ia_override varchar(255)
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_schedules_id_seq');
		Database::query('ALTER SEQUENCE sss_schedules_id_seq OWNED BY sss_schedules.id');
		Database::query("ALTER TABLE sss_schedules ALTER COLUMN id SET DEFAULT nextval('sss_schedules_id_seq')");
	}
	Database::query("ALTER TABLE sss_schedules ADD CONSTRAINT sss_schedules_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
}

if (!Database::tableExists('sss_services')) {
	Database::query("
		CREATE TABLE sss_services (
			id bigint {$identity} primary key NOT NULL,
			category varchar(255) NOT NULL,
			subject varchar(255) NOT NULL,
			duration integer NOT NULL,
			location varchar(255) NOT NULL,
			determined_by varchar(255) NOT NULL,
			modified_instruction {$boolean} NOT NULL,
			consult varchar(255),
			schedule_id bigint NOT NULL,
			frequency varchar(255) DEFAULT 'Weekly' NOT NULL,
			multiplier integer DEFAULT 5 NOT NULL,
			type varchar(255) DEFAULT 'direct'
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_services_id_seq');
		Database::query('ALTER SEQUENCE sss_services_id_seq OWNED BY sss_services.id');
		Database::query("ALTER TABLE sss_services ALTER COLUMN id SET DEFAULT nextval('sss_services_id_seq')");
	}
	Database::query("ALTER TABLE sss_services ADD CONSTRAINT sss_services_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES sss_schedules(id);");
}

if (!Database::tableExists('sss_students')) {
	Database::query("
		CREATE TABLE sss_students (
			student_id numeric NOT NULL,
			referred_at date,
			case_manager numeric,
			exited_at date,
			status varchar(255) DEFAULT 'referral' NOT NULL,
			program varchar(255) DEFAULT 'sped' NOT NULL,
			created_at date DEFAULT '2016-10-04' NOT NULL,
			reason varchar(255)
		)
	");

	Database::query("ALTER TABLE sss_students ADD CONSTRAINT sss_sped_referrals_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id);");
	Database::query("ALTER TABLE sss_students ADD CONSTRAINT sss_students_case_manager_foreign FOREIGN KEY (case_manager) REFERENCES users(staff_id);");
}

if (!Database::tableExists('sss_teacher_caseload')) {
	Database::query("
		CREATE TABLE sss_teacher_caseload (
			id bigint {$identity} primary key NOT NULL,
			student_id numeric NOT NULL,
			staff_id numeric NOT NULL,
			accepted {$boolean} DEFAULT {$false} NOT NULL,
			acknowledged {$boolean} DEFAULT {$false} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_teacher_caseload_id_seq');
		Database::query('ALTER SEQUENCE sss_teacher_caseload_id_seq OWNED BY sss_teacher_caseload.id');
		Database::query("ALTER TABLE sss_teacher_caseload ALTER COLUMN id SET DEFAULT nextval('sss_teacher_caseload_id_seq')");
	}
	Database::query("ALTER TABLE sss_teacher_caseload ADD CONSTRAINT sss_teacher_caseload_student_id_staff_id_unique UNIQUE (student_id, staff_id)");
	Database::query("ALTER TABLE sss_teacher_caseload ADD CONSTRAINT sss_teacher_caseload_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES users(staff_id);");
	Database::query("ALTER TABLE sss_teacher_caseload ADD CONSTRAINT sss_teacher_casload_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id);");
}

if (!Database::tableExists('sss_trigger_instances')) {
	Database::query("
		CREATE TABLE sss_trigger_instances (
			id bigint {$identity} primary key NOT NULL,
			event_instance_id bigint NOT NULL,
			trigger_id bigint NOT NULL,
			trigger_time {$timestamp},
			params {$text},
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_trigger_instances_id_seq');
		Database::query('ALTER SEQUENCE sss_trigger_instances_id_seq OWNED BY sss_trigger_instances.id');
		Database::query("ALTER TABLE sss_trigger_instances ALTER COLUMN id SET DEFAULT nextval('sss_trigger_instances_id_seq')");
	}
	Database::query("ALTER TABLE sss_trigger_instances ADD CONSTRAINT sss_trigger_instances_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
	Database::query("ALTER TABLE sss_trigger_instances ADD CONSTRAINT sss_trigger_instances_trigger_id_foreign FOREIGN KEY (trigger_id) REFERENCES sss_event_triggers(id);");
}

if (!Database::tableExists('sss_user_assignments')) {
	Database::query("
		CREATE TABLE sss_user_assignments (
			id bigint {$identity} primary key NOT NULL,
			user_id numeric NOT NULL,
			event_instance_id bigint,
			step_instance_id bigint,
			added_by numeric NOT NULL,
			note {$text},
			permissions {$text} DEFAULT 'edit' NOT NULL,
			created_at {$timestamp} NOT NULL,
			updated_at {$timestamp} NOT NULL,
			data {$text},
			casemanager {$boolean}
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_user_assignments_id_seq');
		Database::query('ALTER SEQUENCE sss_user_assignments_id_seq OWNED BY sss_user_assignments.id');
		Database::query("ALTER TABLE sss_user_assignments ALTER COLUMN id SET DEFAULT nextval('sss_user_assignments_id_seq')");
	}
	Database::query("ALTER TABLE sss_user_assignments ADD CONSTRAINT sss_user_assignments_added_by_foreign FOREIGN KEY (added_by) REFERENCES users(staff_id);");
	Database::query("ALTER TABLE sss_user_assignments ADD CONSTRAINT sss_user_assignments_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");
	Database::query("ALTER TABLE sss_user_assignments ADD CONSTRAINT sss_user_assignments_step_instance_id_foreign FOREIGN KEY (step_instance_id) REFERENCES sss_event_step_instances(id);");
	Database::query("ALTER TABLE sss_user_assignments ADD CONSTRAINT sss_user_assignments_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(staff_id);");
}
