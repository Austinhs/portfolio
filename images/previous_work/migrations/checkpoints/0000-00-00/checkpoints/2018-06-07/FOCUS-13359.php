<?php

Migrations::depend('FOCUS-8295a');
Migrations::depend('FOCUS-8295b');
Migrations::depend('FOCUS-8295c');

if (Database::$type !== "postgres") {
	return true;
}

$version = Database::get("SELECT version() AS version");
$version = $version[0]['VERSION'];
$version = explode(' ', $version)[1];
$pivot = strpos($version, '.');
$major = (int)substr($version, 0, $pivot);

if ($major < 9) {
	return false;
}

$minor = (int)substr($version, $pivot + 1);
$minorPivot = strpos($minor, '.');

if ($minorPivot !== false) {
	$minor = substr($minor, 0, $minorPivot);
}

$minor = (int)$minor;
if ($major < 10 && $minor < 4) {
	return false;
}

try {
	Database::query("ALTER TABLE formbuilder_data ALTER COLUMN value TYPE jsonb USING value::jsonb");
	Database::query("ALTER TABLE formbuilder_objects ALTER COLUMN object TYPE jsonb USING object::jsonb");
} catch (\Exception $e) {
	// Running old version of postgres that doesn't support jsonb yet
	return false;
}
