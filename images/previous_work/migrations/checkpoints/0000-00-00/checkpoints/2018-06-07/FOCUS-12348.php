<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

Database::query("UPDATE sss_services SET location = 'ESE' WHERE location = 'SE'");
