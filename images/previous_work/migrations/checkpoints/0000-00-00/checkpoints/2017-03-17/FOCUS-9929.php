<?php

if(!Database::tableExists('ps_fee_groups') || Database::$type !== 'mssql') {
	return false;
}

$columns = [
	'course_id',
	'subject_id',
	'school_id',
	'template_id'
];

$tables = [
	'ps_fee_groups',
	'ps_fee_templates_joins'
];

foreach($tables as $table) {
	foreach($columns as $column) {
		Database::query("
			ALTER TABLE
				{$table}
			ALTER COLUMN
				{$column}
			BIGINT
				NULL
		");
	}
}
