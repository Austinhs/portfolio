<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::columnExists('sss_domains', 'form_field')) {
	Database::createColumn('sss_domains', 'form_field', 'varchar', 255);
}
