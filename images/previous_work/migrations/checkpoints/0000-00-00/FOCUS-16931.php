<?php

// Tags: SSS

if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return;
}

if (!Database::columnExists('sss_event_step_instances', 'drafted')) {
	Database::createColumn('sss_event_step_instances', 'drafted', 'date');
}
