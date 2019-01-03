<?php

Migrations::depend('FOCUS-5468');

$cols = [
	'total'          => 'bigint',
	'primary_sort'   => 'bigint',
	'secondary_sort' => 'bigint',
	'computed_query' => 'text'
];

foreach($cols as $col => $type) {
	if(Database::columnExists('linked_fields', $col)) {
		Database::query("ALTER TABLE linked_fields DROP COLUMN {$col}");
	}

	if(!Database::columnExists('custom_field_log_columns', $col)) {
		Database::createColumn('custom_field_log_columns', $col, $type);
	}
}
