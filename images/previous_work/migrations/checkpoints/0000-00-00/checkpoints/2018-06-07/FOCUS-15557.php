<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

Database::query("ALTER TABLE sss_event_triggers ADD FOREIGN KEY (event_id) REFERENCES sss_events(id)");
