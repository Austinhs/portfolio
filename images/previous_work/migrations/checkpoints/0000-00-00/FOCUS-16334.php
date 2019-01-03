<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

Database::query(Database::preprocess("UPDATE sss_events SET name = {{trim:name}}"));
