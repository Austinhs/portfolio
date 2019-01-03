<?php

if (Database::$type === 'mssql') {
		Database::query(
		"ALTER TABLE
			students
		ADD
			password_token varchar(100)
	");
} else {
	Database::query(
		"ALTER TABLE
			students
		ADD COLUMN
			password_token varchar(100)
		");
}
