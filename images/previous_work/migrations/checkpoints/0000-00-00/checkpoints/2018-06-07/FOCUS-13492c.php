<?php

if (defined('EXPORT_SITE') && EXPORT_SITE === true) {
	return false;
}

// Disable for Indian River and Jackson respectively.
if ($GLOBALS['ClientId'] === 20769 || $GLOBALS['ClientId'] === 18069) {
	return false;
}

if (!class_exists('Focus\ExportBuilder\Entity')) {
	throw new \Exception('Please run composer install.');
}

// TODO: don't piggy back off migrations, create a post hook somehow on migrations/manage + updater
Migrations::depend('FOCUS-14432');
Migrations::depend('FOCUS-14635');
Migrations::depend('FOCUS-14635b');
Migrations::depend('FOCUS-13732');
Migrations::depend('FOCUS-14926');
Migrations::depend('FOCUS-15557');
Migrations::depend('FOCUS-16071');

// Tags: SSS
$sss_disabled = !defined('SSS_ENABLED') || SSS_ENABLED !== true;

if ($sss_disabled || Database::$type === "mssql") {
	return false;
}

set_time_limit(0);

Database::isolate(function() {
	global $staticpath;

	require_once(rtrim($staticpath, '/').'/modules/ExportBuilder/Importer.php');
});

return false;
