<?php

$tables = [
	'custom_fields',
	'custom_field_log_columns'
];

// Updates default value and fallback value of select multiple from broken to NULL
foreach($tables as $table) {
	$sql = "
		UPDATE
			{$table}
		SET
			default_value = (CASE WHEN default_value = :empty THEN NULL ELSE default_value END),
			fallback_value = (CASE WHEN fallback_value = :empty THEN NULL ELSE fallback_value END)
		WHERE
			type = :type
	";

	$params = [
		'empty' => '[""]',
		'type'  => 'multiple',
	];

	Database::query($sql, $params);
}
