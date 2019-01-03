<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

$key     = 'enable_2_objectives_per_goal';
$enabled = Database::get("SELECT 1 FROM sss_config WHERE name = '{$key}'");

if (empty($enabled)) {
	$staff_id = Authenticate::getUserId();
	$now      = DBDate('timestamp');

	$record = [
		'last_changed_user' => $staff_id,
		'value'             => 'true',
		'name'              => $key,
		'created_at'        => $now,
		'updated_at'        => $now
	];

	Database::insert('sss_config', null, array_keys($record), [ $record ]);
}
