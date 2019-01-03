<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::tableExists('sss_progress_codes')) {
	$identity  = Database::$type === 'mssql' ? 'IDENTITY' : '';
	$timestamp = Database::$type == 'mssql' ? 'DATETIME' : 'TIMESTAMP';
	$text      = Database::$type == 'mssql' ? 'VARCHAR(MAX)' : 'TEXT';

	Database::query("
		CREATE TABLE sss_progress_codes (
			id BIGINT {$identity} PRIMARY KEY NOT NULL,
			code varchar(255),
			description varchar(255)
		)
	");

	if (Database::$type === 'postgres') {
		Database::createSequence('sss_progress_codes_id_seq');
		Database::query('ALTER SEQUENCE sss_progress_codes_id_seq OWNED BY sss_progress_codes.id');
		Database::query("ALTER TABLE sss_progress_codes ALTER COLUMN id SET DEFAULT nextval('sss_progress_codes_id_seq')");
	}

	Database::query("
		CREATE TABLE sss_progress_updates (
			id BIGINT {$identity} PRIMARY KEY NOT NULL,
			event_instance_id BIGINT NOT NULL,
			domain_id BIGINT NOT NULL,
			goal_id BIGINT NOT NULL,
			progress_code_id BIGINT,
			progress_date {$timestamp},
			meeting_goal varchar(1),
			comments {$text}
		)
	");

	Database::query("ALTER TABLE sss_progress_updates ADD CONSTRAINT sss_progress_goal_id_foreign FOREIGN KEY (goal_id) REFERENCES sss_goals(id);");
	Database::query("ALTER TABLE sss_progress_updates ADD CONSTRAINT sss_progress_domain_id_foreign FOREIGN KEY (domain_id) REFERENCES sss_domains(id);");
	Database::query("ALTER TABLE sss_progress_updates ADD CONSTRAINT sss_progress_progress_code_id_foreign FOREIGN KEY (progress_code_id) REFERENCES sss_progress_codes(id);");
	Database::query("ALTER TABLE sss_progress_updates ADD CONSTRAINT sss_progress_event_instance_id_foreign FOREIGN KEY (event_instance_id) REFERENCES sss_event_instances(id);");


	if (Database::$type === 'postgres') {
		Database::createSequence('sss_progress_updates_id_seq');
		Database::query('ALTER SEQUENCE sss_progress_updates_id_seq OWNED BY sss_progress_updates.id');
		Database::query("ALTER TABLE sss_progress_updates ALTER COLUMN progress_date SET DEFAULT NOW()");
		Database::query("ALTER TABLE sss_progress_updates ALTER COLUMN id SET DEFAULT nextval('sss_progress_updates_id_seq')");
	}
}
