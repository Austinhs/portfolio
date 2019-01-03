<?php

// Drop the email column of the users table
if(Database::columnExists('users', 'email')) {
	$cascade = Database::$type === 'postgres' ? 'CASCADE' : '';

	Database::query("
		ALTER TABLE
			users
		DROP COLUMN
			email {$cascade}
	");
}
