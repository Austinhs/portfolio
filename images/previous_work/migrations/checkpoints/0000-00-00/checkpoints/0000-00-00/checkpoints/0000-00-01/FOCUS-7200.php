<?php

// Update the 'output' column of the 'database_migrations' table for the existing migrations
$template = json_encode([
	'sql' => [
		'sql_log'         => null,
		'sql_times'       => null,
		'sql_fetch_times' => null,
		'sql_cache_times' => null
	],

	'output' => '~~~OUTPUT~~~'
]);

$sql = "
	UPDATE
		database_migrations
	SET
		output = REPLACE(:template, '~~~OUTPUT~~~', COALESCE(output, ''))
	WHERE
		output IS NULL OR output NOT LIKE '{%}'
";

$params = [
	'template' => $template
];

Database::query($sql, $params);
