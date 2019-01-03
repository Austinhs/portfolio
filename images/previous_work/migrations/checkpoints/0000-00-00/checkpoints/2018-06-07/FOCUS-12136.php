<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::columnExists('sss_schedules', 'title')) {
	Database::createColumn('sss_schedules', 'title', 'varchar', 255);
}

if (!Database::columnExists('sss_accommodations', 'schedule')) {
	Database::createColumn('sss_accommodations', 'schedule', 'varchar', 255);
}
