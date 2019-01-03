<?php

if(!Database::columnExists('grad_subjects', 'request_group')) {
	Database::createColumn('grad_subjects', 'request_group', 'int');

	$sql = [
		"UPDATE grad_subjects set request_group = 1 WHERE short_name in ('A1', 'MA', 'GE')",
		"UPDATE grad_subjects set request_group = 2 WHERE short_name in ('BI', 'SC', 'EQ')",
		"UPDATE grad_subjects set request_group = 3 WHERE short_name in ('SS', 'WH', 'AH', 'EC', 'AG')",
		"UPDATE grad_subjects set request_group = 4 WHERE short_name in ('EN')"
	];

	foreach($sql as $tmp_sql) {
		Database::query($tmp_sql);
	}
}
