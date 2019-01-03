<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

if (!Database::columnExists("sss_progress_updates", "objective_id")) {
	Database::createColumn("sss_progress_updates", "objective_id", "bigint");
}

if (!Database::columnExists("sss_progress_updates", "review_period")) {
	Database::createColumn( "sss_progress_updates", "review_period", "bigint");
}

if (!Database::columnExists("sss_goals", "locked_progress_periods")) {
	Database::createColumn("sss_goals", "locked_progress_periods", "text");
}

if (Database::columnExists("sss_progress_updates", "domain_id")) {
	Database::query("ALTER TABLE sss_progress_updates DROP CONSTRAINT sss_progress_domain_id_foreign");
	Database::dropColumn("sss_progress_updates", "domain_id");
}

$permission = Database::get("SELECT id FROM sss_permissions WHERE short_name = 'lock_progress_periods'");

// This is now in the SSS installer/syncer
// if (count($permission) == 0) {
// 	$columns = [
// 		'name',
// 		'description',
// 		'category',
// 		'short_name',
// 		'program_id',
// 		'created_at',
// 		'updated_at'
// 	];
//
// 	$values = [[
// 		'name'        => 'Lock Objectives Progress Periods',
// 		'description' => 'Allows locking of progress periods of objectives when progress monitoring goals',
// 		'category'    => 'system',
// 		'short_name'  => 'lock_progress_periods',
// 		'program_id'  => 0,
// 		'created_at'  => date('Y-m-d'),
// 		'updated_at'  => date('Y-m-d')
// 	]];
//
// 	$sequence = Database::$type == 'mssql' ? null : 'sss_permissions_id_seq';
//
// 	Database::insert("sss_permissions", $sequence, $columns, $values);
// }
