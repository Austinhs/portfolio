<?php

// Don't run this until 8.0. It's in 7.0 to guarantee that it will be run before any 8.0 migrations.
if(!class_exists('CustomFieldObject')) {
	return false;
}

if(!Database::columnExists('database_object_log', 'params')) {
	Database::createColumn('database_object_log', 'params', 'text');
}
