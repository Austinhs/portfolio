<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$tables = [
	'gl_ap_invoice_allocation' => [
		'demand',
		'kw_usage',
		'gal_usage'
	],
	'gl_ap_invoice' => [
		'utility'
	]
];

Database::begin();

foreach($tables as $table => $columns) {
	foreach($columns as $column) {
		if(!Database::columnExists($table, $column)) {
			Database::createColumn($table, $column, 'INT');
		}
	}
}

Database::commit();
