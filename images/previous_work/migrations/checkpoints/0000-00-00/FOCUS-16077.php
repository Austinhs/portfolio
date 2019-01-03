<?php

if(!Database::columnExists('gradebook_assignments', 'publish_date')) {
	Database::createColumn('gradebook_assignments', 'publish_date', 'timestamp');

	$sql = [
		"UPDATE gradebook_assignments set publish_date=assigned_date ",
	];

	foreach($sql as $tmp_sql) {
		Database::query($tmp_sql);
	}
}
