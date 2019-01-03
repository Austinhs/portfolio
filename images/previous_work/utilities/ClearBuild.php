<?php

require_once __DIR__ . '/../Warehouse.php';

if(!Permissions::hasPermission('School_Setup/SystemPreferences.php:can_edit')) {
	die();
}

if(isset($GLOBALS['_FOCUS']['CompressorBuildPath'])) {
	$path = realpath($GLOBALS['_FOCUS']['CompressorBuildPath']);
}
else {
	$path = realpath($GLOBALS['staticpath']) . '/build';
}

if(!is_dir($path) || basename($path) !== 'build' || !is_writable($path)) {
	throw new Exception("Please check the CompressorBuildPath.");
}

$path = escapeshellarg($path);
$cmd  = "rm -rf {$path}/*";

echo `{$cmd}`;
echo '-- Successfully cleared build folder';
