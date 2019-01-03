<?php

if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

// As always, postgres works like a charm
if (Database::$type === "postgres") {
	return true;
}

Database::query("ALTER TABLE sss_programs ALTER COLUMN created_at DATETIME2(0)");
Database::query("ALTER TABLE sss_programs ALTER COLUMN updated_at DATETIME2(0)");
Database::query("ALTER TABLE sss_programs ALTER COLUMN deleted_at DATETIME2(0)");

Database::query("ALTER TABLE sss_events ALTER COLUMN created_at DATETIME2(0)");
Database::query("ALTER TABLE sss_events ALTER COLUMN updated_at DATETIME2(0)");
Database::query("ALTER TABLE sss_events ALTER COLUMN deleted_at DATETIME2(0)");
