<?php
$urls = Database::get("SELECT ID, PHP_SELF FROM SAVED_REPORTS");

$patterns[0] = '~month_include_active_date=[A-Z]{3}&amp;~';
$patterns[1] = '~day_include_active_date=[0-9]{2}&amp;~';
$patterns[2] = '~year_include_active_date=[0-9]{2}&amp;~';
$patterns[3] = '~month_include_active_date=[A-Z]{3}&~';
$patterns[4] = '~day_include_active_date=[0-9]{2}&~';
$patterns[5] = '~year_include_active_date=[0-9]{2}&~';

$replacements[0] = '';
$replacements[1] = '';
$replacements[2] = '';
$replacements[3] = '';
$replacements[4] = '';
$replacements[5] = '';

foreach($urls as $url) {
	$res = preg_replace($patterns, $replacements, $url['PHP_SELF']);

	$update_sql = "UPDATE
		SAVED_REPORTS
	SET
		PHP_SELF = :PHP_SELF
	WHERE
		ID = :ID
	";

	$update_params = [
	'PHP_SELF' => $res,
	'ID'       => $url['ID'],
	];

	Database::query($update_sql, $update_params);
}