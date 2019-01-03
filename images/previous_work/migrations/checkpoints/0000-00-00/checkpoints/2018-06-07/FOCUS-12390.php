<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::columnExists("sss_events", "tags")) {
	Database::createColumn("sss_events", "tags", 'varchar', 255);
}
