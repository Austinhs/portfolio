<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::columnExists('sss_goals', 'unknown_column1')) {
	Database::createColumn('sss_goals', 'unknown_column1', 'varchar', 255);
}
