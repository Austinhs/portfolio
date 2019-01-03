<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5469');
Migrations::depend('FOCUS-6197');

if(!Database::columnExists('edit_rules', 'sql')) {
	Database::createColumn('edit_rules', 'sql', 'text');
}

if(!Database::columnExists('edit_rules', 'new_object')) {
	Database::createColumn('edit_rules', 'new_object', 'bigint');
}
