<?php
if (file_exists(dirname(__DIR__).'/modules/Florida_Reports/menu_include.php')) {
	include(dirname(__DIR__).'/modules/Florida_Reports/menu_include.php');
}
if (file_exists(dirname(__DIR__).'/modules/Texas_Reports/menu_include.php')) {
	include(dirname(__DIR__).'/modules/Texas_Reports/menu_include.php');
}

if(!empty($_FOCUS['config']['SCHEDULE'])) {
	foreach ($_FOCUS['config']['SCHEDULE'] as $key => $title) {
		if (Database::$type === 'mssql') {
			$key_col = '[KEY]';
		} else {
			$key_col = 'KEY';
		}
		Database::query(
			"INSERT INTO
				PERMISSION (PROFILE_ID, {$key_col})
				SELECT PROFILE_ID, 'SISSched:{$key}:can_edit' as {$key_col}
				FROM PERMISSION
				WHERE {$key_col} = 'Scheduling/Schedule.php:can_edit'"
		);
		Database::query(
			"INSERT INTO
				PERMISSION (PROFILE_ID, {$key_col})
				SELECT PROFILE_ID, 'SISSched:{$key}:can_view' as {$key_col}
				FROM PERMISSION
				WHERE {$key_col} = 'Scheduling/Schedule.php:can_view'"
		);
	}
}
