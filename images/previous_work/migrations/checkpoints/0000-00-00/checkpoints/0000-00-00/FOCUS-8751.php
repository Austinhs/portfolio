<?php
if (Database::$type == 'mssql') {
	Database::query("ALTER TABLE GRAD_YEAR_REQUIREMENTS ALTER COLUMN CUSTOM_LIST varchar(max) NULL");
}