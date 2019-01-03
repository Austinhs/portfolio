<?php

// Tags: Formbuilder
// Migrations doesn't properly rollback migrations, so we check column here
if (!Database::columnExists('gl_requests', 'created_by_class')) {
	Database::createColumn('gl_requests', 'created_by_class', 'varchar');
	Database::query("UPDATE gl_requests SET created_by_class = 'SISUser'");
}
