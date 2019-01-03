<?php

Migrations::depend('FOCUS-5468');

// Add some columns to the custom_field_categories table
$cols = [
	'default_profiles_view' => 'text',
	'default_profiles_edit' => 'text'
];

foreach($cols as $col => $type) {
	if(!Database::columnExists('custom_field_categories', $col)) {
		Database::createColumn('custom_field_categories', $col, $type);
	}
}
