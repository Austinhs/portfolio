<?php


Database::query("
	UPDATE
		custom_fields
	SET
		required=NULL
	WHERE
		type='computed_table'
		OR type='computed'
		OR type='file'
");

Database::query("
	UPDATE
		custom_field_log_columns
	SET
		required=NULL
	WHERE
		type='computed_table'
		OR type='computed'
		OR type='file'
");