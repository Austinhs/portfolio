<?php

if(Database::tableExists('school_fields')) {
	Database::query("
	UPDATE
		school_fields
	SET
		max_length = 4
	WHERE
		id = 100000007;
	");
} else {
	Database::query("
	UPDATE
		custom_fields
	SET
		max_length = 4
	WHERE
		column_name = 'custom_100000007';
	");
}
