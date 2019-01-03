<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::columnExists("sss_accommodations", "extended_time")) {
	Database::createColumn("sss_accommodations", "extended_time", "text");
}
