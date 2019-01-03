<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

Database::query("UPDATE sss_accommodations SET location = CONCAT('[\"', location, '\"]')");
Database::query("UPDATE sss_accommodations_other SET location = CONCAT('[\"', location, '\"]')");
