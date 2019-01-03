<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return;
}

if (!Database::columnExists('sss_progress_updates', 'deleted_at')) {
	Database::createColumn('sss_progress_updates', 'deleted_at', 'timestamp');
}
