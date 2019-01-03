<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

// Createa  table placeholder until metadata
// can handle the table and install indexes and etc
if(!Database::tableExists('gl_ia_account_range')) {
	Database::query('
		create table gl_ia_account_range (
		id bigint,
		deleted bigint,
		range varchar(255),
		code varchar(255),
		title varchar(255)
		)
	');
}
// Prepolating some settings with some expected defaults:
// A front end interface to edit these exists

$ranges = [
	[
		'range' => '1',
		'code'  => '10000',
		'title' => 'Athletics'
	],
	[
		'range' => '2',
		'code'  => '20000',
		'title' => 'Music'
	],
	[
		'range' => '3',
		'code'  => '30000',
		'title' => 'Classes'
	],
	[
		'range' => '4',
		'code'  => '40000',
		'title' => 'Clubs'
	],
	[
		'range' => '5',
		'code'  => '50000',
		'title' => 'Departments'
	],
	[
		'range' => '6',
		'code'  => '60000',
		'title' => 'Trust'
	],
	[
		'range' => '7',
		'code'  => '70000',
		'title' => 'General'
	]
];

foreach($ranges as $range) {
	(new IAAccountRange)
		->setRange($range['range'])
		->setCode($range['code'])
		->setTitle($range['title'])
		->persist();
}
