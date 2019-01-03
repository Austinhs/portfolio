<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

$create_columns = ['transportation_code', 'initiation_date', 'duration_date'];

foreach ($create_columns as $column) {
	if (!Database::columnExists('sss_services', $column)) {
		Database::createColumn('sss_services', $column, 'varchar', 255);
	}
}

if (Database::columnExists('sss_services', 'multiplier')) {
	if (Database::$type === "mssql") {
		$constraint_name = Database::get("Select [Name] From Sys.Objects Where [Name] like 'DF__sss_servi__multi__%'");
		if (!empty($constraint_name)) {
			$constraint_name = $constraint_name[0]['NAME'];
			Database::query("ALTER TABLE sss_services DROP CONSTRAINT {$constraint_name}");
		}
	}
	Database::dropColumn('sss_services', 'multiplier');
}

if (Database::$type !== "mssql") {
	Database::query("ALTER TABLE sss_services ALTER COLUMN frequency DROP NOT NULL");
} else {
	Database::query("ALTER TABLE sss_services ALTER COLUMN frequency varchar(255) NULL");
}

Database::query("UPDATE sss_services SET frequency = null");
Database::query("UPDATE sss_services SET duration = 0 WHERE category = 'supplemental'");
