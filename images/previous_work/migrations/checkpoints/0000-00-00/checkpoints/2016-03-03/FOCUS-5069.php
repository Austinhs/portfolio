<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5468');

if (isset($GLOBALS['_FOCUS']['config']['state_name']) && $GLOBALS['_FOCUS']['config']['state_name'] == 'Texas') {
	$field_map = array(
		'Endorsements' => 'varchar(100)',
		'On Track (Arts & Humanities)' => 'varchar(1)',
		'On Track (Business & Industry)' => 'varchar(1)',
		'On Track (Multi-Disciplinary Studies)' => 'varchar(1)',
		'On Track (Public Service)' => 'varchar(1)',
		'On Track (STEM)' => 'varchar(1)',
	);
	foreach ($field_map as $title => $type) {
		if (class_exists('CustomFieldObject')) {
			$field_id_col = 'SUBSTRING(COLUMN_NAME,8,50) as ID';
		} else {
			$field_id_col = 'ID';
		}
		$field_id_RET = DBGet(DBQuery(
			"SELECT {$field_id_col}
			FROM CUSTOM_FIELDS
			WHERE TITLE='{$title}'"
		));
		if ($GLOBALS['DatabaseType']=='mssql') {
			$column = "";
		} else {
			$column = "COLUMN ";
		}
		if (!empty($field_id_RET[1]['ID'])) {
			$table_name  = "students";
			$column_name = "custom_{$field_id_RET[1]['ID']}";

			if(!Database::columnExists($table_name, $column_name)) {
				DBQuery("ALTER TABLE {$table_name} ADD {$column}{$column_name} {$type}", true, false, true, true);
			}
		}
	}
}
