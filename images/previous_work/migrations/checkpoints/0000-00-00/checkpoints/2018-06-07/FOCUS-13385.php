<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (Database::$type == 'mssql') {
	Database::query("ALTER TABLE sss_event_steps ALTER COLUMN handler VARCHAR(MAX) NULL");
}
else {
	Database::query("ALTER TABLE sss_event_steps ALTER COLUMN handler DROP NOT NULL");
}

Database::query("UPDATE sss_event_steps SET handler = NULL WHERE handler = 'default'");
Database::query("UPDATE sss_events SET handler = NULL WHERE handler = 'sss/events/steps'");
