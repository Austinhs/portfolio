<?php

if (Database::$type == 'mssql') {
	Database::changeColumnType('saved_reports', 'php_self', 'VARCHAR', '(max)');
}
