<?php

$tables = [
	"address_field_flags" => [
		"contact_detail_title" => ["VARCHAR", 1],
	],
];

foreach($tables as $table => $columns) {
	foreach($columns as $column => $definition) {
		$type     = array_shift($definition);
		$length   = array_shift($definition);
		$nullable = array_shift($definition);

		if($nullable === null) {
			$nullable = true;
		}

		if(!Database::columnExists($table, $column)) {
			Database::createColumn($table, $column, $type, $length, $nullable);
		}
	}
}

// Only add this for Collier County
if($GLOBALS['ClientId'] === 18130 && Database::tableExists('address_field_flags')) {
	(new AddressFieldFlags())
		->setTitle('CCPS UID')
		->setDatabaseColumnName('ccpsuid')
		->setContactDetailTitle('1')
		->persist();
}
