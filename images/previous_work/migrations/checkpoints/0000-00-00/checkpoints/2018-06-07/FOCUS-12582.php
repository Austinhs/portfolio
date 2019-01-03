<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

$value = Database::get("SELECT * FROM sss_config WHERE name = 'pdf_generator'");
if (empty($value)) {
	$records = [
		[
			'name'              => 'pdf_generator',
			'value'             => 'browser',
			'created_at'        => Carbon\Carbon::now(),
			'updated_at'        => Carbon\Carbon::now(),
			'last_changed_user' => $_SESSION['USER_CLASS_ID']
		]
	];

	Database::insert('sss_config', null, array_keys($records[0]), $records);
}
