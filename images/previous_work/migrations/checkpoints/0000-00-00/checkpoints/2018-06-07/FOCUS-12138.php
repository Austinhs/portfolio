<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (Database::$type === 'postgres') {
	$text      = 'text';
} else {
	$text      = 'varchar(max)';
}

if (!Database::tableExists('sss_accommodations_other')) {
	Database::createSequence('sss_accommodations_other_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE sss_accommodations_other (
			id BIGINT PRIMARY KEY DEFAULT {{next:sss_accommodations_other_id_seq}},
			event_instance_id BIGINT REFERENCES sss_event_instances ON DELETE CASCADE,
			accommodation {$text},
			duration varchar(255),
			frequency varchar(255),
			location varchar(255)
		)
	"));
}
