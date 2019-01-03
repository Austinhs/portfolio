<?php

// Tags: Formbuilder
if (!Database::columnExists('gl_requests', 'school_id')) {
	Database::createColumn('gl_requests', 'school_id', 'bigint');
}

Database::query("UPDATE gl_requests SET school_id = 0");
