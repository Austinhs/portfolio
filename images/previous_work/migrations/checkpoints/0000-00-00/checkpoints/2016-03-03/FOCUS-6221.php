<?php

Migrations::depend('FOCUS-6359');

$cols = [
	'phone_unlisted',
	'phone_callout',
	'phone_blocked'
];

foreach($cols as $col) {
	if(!Database::columnExists('address', $col)) {
		Database::createColumn('address', $col, 'bigint');
	}
}
