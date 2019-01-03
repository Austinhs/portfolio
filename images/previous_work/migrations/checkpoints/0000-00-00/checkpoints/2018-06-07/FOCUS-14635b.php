<?php

// Tags: Formbuilder
if (Database::$type == 'mssql') {
	Database::query("ALTER TABLE formbuilder_revisions ALTER COLUMN author_id NUMERIC NULL");
}
else {
	Database::query("ALTER TABLE formbuilder_revisions ALTER COLUMN author_id DROP NOT NULL");
}
