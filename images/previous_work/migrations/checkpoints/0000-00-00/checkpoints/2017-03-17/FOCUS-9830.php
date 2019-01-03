<?php

$indexes = [
	'address_to_district' => [
		'syear'      => 'address_to_district_syear_ix',
		'zip'        => 'address_to_district_zip_ix',
		'high_range' => 'address_to_district_high_range_ix',
		'low_range'  => 'address_to_district_low_range_ix',
	]
];

foreach($indexes as $table_name => $tmp_indexes) {
	foreach($tmp_indexes as $column_name => $index_name) {
		if(!Database::indexExists($table_name, $index_name)) {
			Database::query("
				CREATE INDEX
					\"{$index_name}\"
				ON
					\"{$table_name}\"(\"{$column_name}\")
			");
		}
	}
}
