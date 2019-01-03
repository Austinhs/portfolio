<?php

$root = realpath(__DIR__ . '/..');

// Require the config file
require_once("{$root}/config.inc.php");

ini_set('display_errors', true);
error_reporting(E_ALL);
chdir("{$root}/laravel");

// Run composer install
$php            = defined('UPDATER_PHP_CLI_PATH') ? UPDATER_PHP_CLI_PATH : 'php';
$install_output = [];

exec("{$php} composer.phar install 2>&1", $install_output);

echo '<h1>Composer Install</h1>';
echo '<pre>' .print_r($install_output, true) . '</pre>';

// Clear cache directories
$cache_output = [];

foreach(['cache', 'views'] as $dir) {
	$path = "{$root}/laravel/app/storage/{$dir}";

	if(file_exists($path)) {
		$output = [];

		exec("rm {$path}/*", $output);

		$cache_output[$dir] = !empty($output) ? $output : "Cache files deleted in app/storage/{$dir}";
	}
	else {
		$cache_output[$dir] = "Error: app/storage/{$dir} does not exist";
	}
}

echo '<h1>Clear Cache</h1>';
echo '<pre>' .print_r($cache_output, true) . '</pre>';
