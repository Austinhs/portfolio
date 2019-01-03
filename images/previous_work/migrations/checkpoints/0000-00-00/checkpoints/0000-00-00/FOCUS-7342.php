<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$is_mssql = Database::$type == 'mssql';

$columns = array(
	'vehicle_id'           => 'varchar(255)',
	'license_plate_number' => 'varchar(255)',
	'title_number'         => 'varchar(255)',
	'warranty_date'        => ($is_mssql) ? 'datetime' : 'timestamp',
	'computer_role'        => 'bigint',
	'computer_type'        => 'bigint',
);

foreach($columns as $column_name => $column_type) {
	if(!Database::columnExists('gl_fa_asset', $column_name)) {
		Database::createColumn('gl_fa_asset', $column_name, $column_type);
	}
}

$tables = array(
	"AssetComputerRole" => array(
		"A" => "Admin",
		"S" => "Student",
		"T" => "Teacher",
		"U" => "Unknown",
		"Z" => "N/A"
	),
	"AssetComputerType" => array(
		"D" => "Desktop",
		"L" => "Laptop",
		"T" => "Tablet",
		"Z" => "Not Applicable"
	)
);


foreach($tables as $class_name => $records) {
	$table = $class_name::$table;

	if(!Database::tableExists($table)) {
		$table_query = "
			CREATE TABLE {$table} (
				id BIGINT PRIMARY KEY,
				legacy_value VARCHAR(255),
				title VARCHAR(255),
				deleted BIGINT
			)
		";

		Database::query($table_query);
	}

	foreach($records as $legacy => $value) {
		(new $class_name)
			->setLegacyValue($legacy)
			->setTitle($value)
			->persist();
	}
}
