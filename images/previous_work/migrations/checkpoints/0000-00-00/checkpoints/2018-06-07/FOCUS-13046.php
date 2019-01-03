<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

Migrations::depend('FOCUS-12136');

Database::query("UPDATE sss_schedules SET title = '' WHERE title IN ('S1', 'S2')");
Database::query("UPDATE sss_schedules SET title = 'Additional Schedule of Services' WHERE title = 'Next Year'");
Database::query("UPDATE sss_schedules SET title = 'IEP Duration' WHERE title = 'Current School Year'");
