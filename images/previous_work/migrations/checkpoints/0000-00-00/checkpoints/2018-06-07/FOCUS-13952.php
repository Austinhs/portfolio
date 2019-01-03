<?php

// Replace '#' (root folder placeholder) with -1
Database::query("
	UPDATE
		custom_reports_folders
	SET
		parent_id = -1
	WHERE
		CAST(parent_id AS VARCHAR(255)) = '#' OR
		parent_id IS NULL
");

// Replace '#' (root folder placeholder) with -1
Database::query("
	UPDATE
		custom_reports
	SET
		parent_id = -1
	WHERE
		CAST(parent_id AS VARCHAR(255)) = '#' OR
		parent_id IS NULL
");

// Correct some data types
Database::changeColumnType('custom_reports_folders', 'parent_id', 'bigint', null, false);
Database::changeColumnType('custom_reports', 'parent_id', 'bigint', null, false);

// Correct some primary keys
$tables = [
	'custom_reports'           => 'id',
	'custom_reports_folders'   => 'id',
	'custom_reports_variables' => 'id',
];

foreach($tables as $table => $column) {
	$pk = Database::getPrimaryKey($table);

	if(!empty($pk)) {
		continue;
	}

	// Drop any unique constraints on the table
	$constraints = Database::getConstraints($table);

	foreach($constraints as $name => $constraint) {
		if($constraint['CONSTRAINT_TYPE'] === 'UNIQUE') {
			Database::query("ALTER TABLE {$table} DROP CONSTRAINT \"{$name}\"");
		}
	}

	// Re-create the ID column and make it a primary key
	$id = uniqid();

	Database::changeColumnType($table, $column, 'bigint', '', false);
	Database::query("ALTER TABLE {$table} ADD PRIMARY KEY ({$column})");
}

// Fix some sequences
$sequences = [
	'custom_reports'           => 'custom_reports_seq',
	'custom_reports_folders'   => 'custom_reports_folders_seq',
	'custom_reports_variables' => 'custom_reports_variables_seq',
	'district_report_runlog'   => 'district_report_runlog_seq',
];

foreach($sequences as $table => $sequence) {
	// Create the sequence if it doesn't exist
	if(!Database::sequenceExists($sequence)) {
		Database::createSequence($sequence);
	}

	// Reset the sequence
	$rows = Database::get("SELECT MAX(id) AS n FROM {$table}");
	$row  = reset($rows);

	if(!empty($row)) {
		$n   = intval($row['N']) + 1;
		$sql = "ALTER SEQUENCE {$sequence} RESTART WITH {$n}";

		Database::query($sql);
	}
}

// Make sure we don't have IDs of 0
$sql = [
	"UPDATE custom_reports SET id = {{next:custom_reports_seq}} WHERE id = 0",
	"UPDATE custom_reports_variables SET id = {{next:custom_reports_variables_seq}} WHERE id = 0",
];

foreach($sql as $tmp_sql) {
	Database::query(Database::preprocess($tmp_sql));
}

// Drop some unused columns
if(Database::columnExists('custom_reports', 'singular')) {
	Database::dropColumn('custom_reports', 'singular');
}

if(Database::columnExists('custom_reports', 'plural')) {
	Database::dropColumn('custom_reports', 'plural');
}

if(Database::columnExists('custom_reports', 'execute_only')) {
	Database::dropColumn('custom_reports', 'execute_only');
}

// Update permissions for SIS district reports
Database::query("
	UPDATE permission
	SET
		\"key\" = REPLACE(\"key\", 'Reports/CustomReports.php', 'Reports/DistrictReports.php')
	WHERE
		\"key\" LIKE '%Reports/CustomReports.php%' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = permission.profile_id AND
				p2.\"key\" =  REPLACE(permission.\"key\", 'Reports/CustomReports.php', 'Reports/DistrictReports.php')
		)
");

// Rename some columns
if(Database::columnExists('custom_reports', 'school_id')) {
	Database::renameColumn('school_id', 'school_ids', 'custom_reports');
}

if(Database::columnExists('custom_reports', 'profiles')) {
	Database::renameColumn('profiles', 'profile_ids', 'custom_reports');
}

// Fix the school_ids column (it stores multiple school IDs)

// Make sure it's nullable
Database::changeColumnType('custom_reports', 'school_ids', 'text', '', true);

// Update "0" to NULL
Database::query("UPDATE custom_reports SET school_ids = NULL WHERE school_ids = '0' OR school_ids = '||0||'");

// Update single IDs to select multiple syntax
Database::query("UPDATE custom_reports SET school_ids = CONCAT('||', school_ids, '||') WHERE school_ids IS NOT NULL AND school_ids NOT LIKE '||%'");
