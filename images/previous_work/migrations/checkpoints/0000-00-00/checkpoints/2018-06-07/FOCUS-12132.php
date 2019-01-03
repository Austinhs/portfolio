<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::columnExists('sss_goals', 'assessment_procedures')) {
	Database::createColumn('sss_goals', 'assessment_procedures', 'varchar', 255);
}
