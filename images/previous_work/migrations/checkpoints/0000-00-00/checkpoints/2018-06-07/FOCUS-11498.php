<?php
// Tags: SSS

if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::columnExists('sss_accommodation_options', 'state_funding')) {
	Database::createColumn('sss_accommodation_options', 'state_funding', 'int');
	Database::query("UPDATE sss_accommodation_options SET state_funding = 1");
}
