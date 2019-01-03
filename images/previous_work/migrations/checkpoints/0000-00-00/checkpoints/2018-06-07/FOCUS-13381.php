<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

Database::query("DELETE FROM permission WHERE \"key\" LIKE 'sss/reports%'");
