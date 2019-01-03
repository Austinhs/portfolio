<?php

// Tags: SSS, Formbuilder
if (!Database::columnExists('formbuilder_instances', 'user_saved')) {
	Database::createColumn('formbuilder_instances', 'user_saved', 'boolean');
}

if (Database::$type === "postgres") {
	$value = "true";
} else {
	$value = 1;
}

Database::query("UPDATE formbuilder_instances SET user_saved = {$value}");

if (Database::$type === "postgres") {
	Database::query("ALTER TABLE formbuilder_instances ALTER COLUMN user_saved SET NOT NULL");
} else {
	Database::query("ALTER TABLE formbuilder_instances ALTER COLUMN user_saved BIT NOT NULL");
}
