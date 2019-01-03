<?php
/**
* @package Focus/SIS
* @copyright Copyright (C) 2006 Andrew Schmadeke. All rights reserved.
* This package can be modified by the licencee, but the license is non-transferrable.
*/

$runningCron            = true;
$running_migrations     = true;
$disable_login_redirect = true;
$dir                    = __DIR__;

// Make sure the DatabaseSession functions are created in the database
require_once(__DIR__ . '/../classes/DatabaseSession.php');
DatabaseSession::$install = true;

require_once("{$dir}/../Warehouse.php");

if(php_sapi_name() !== 'cli' && User('PROFILE') !== 'admin') {
	throw new Exception("You must run this from CLI.");
}

Menu::loadMenuIncludes();

ini_set('display_errors', true);
error_reporting(E_ALL);

if(Task::active()) {
	$args = Task::getArgs();

	if(!isset($args['success']) || !isset($args['paths'])) {
		throw new Exception("Must set 'success' and 'paths' arguments");
	}

	$success = $args['success'];
	$paths   = $args['paths'];

	// Run migrations
	Migrations::migrate($paths);

	// Print the success message
	echo $success;
}
else {
	// For automation always delete a migration that is running through jira
	if(Automation::active()) {
		$migration_id = isset($_GET['migration_id']) ? DBEscapeString($_GET['migration_id']) : false;

		if(!empty($migration_id)) {
			Migrations::delete($migration_id);
		}
	}

	$root  = realpath(__DIR__ . '/..');
	$paths = \Admin\Util::getMigrationPaths($root);

	// Run migrations
	Migrations::migrate($paths);

	// Print the success message
	echo '<span class="migration-complete">Done.</span>';
}
