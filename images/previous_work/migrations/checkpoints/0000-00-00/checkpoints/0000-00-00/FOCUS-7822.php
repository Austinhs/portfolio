<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$sequence = Database::nextSql("gl_maint_seq");

// 8.0 doesn't have the value column
if(Database::columnExists('permission', 'value')) {
	$query = "
		INSERT INTO permission (
			id, profile_id, \"key\", value
		)
		SELECT
			{$sequence},
			profile_id,
			'ap::post_batches',
			1
		FROM
			permission p1
		WHERE
			\"key\" = 'menu::ap_invoices' AND
			NOT EXISTS (
				SELECT
					1
				FROM
					permission p2
				WHERE
					p2.profile_id = p1.profile_id AND
					p2.\"key\" = 'ap::post_batches'
			)
	";
}
else {
	$query = "
		INSERT INTO permission (
			id, profile_id, \"key\"
		)
		SELECT
			{$sequence},
			profile_id,
			'ap::post_batches'
		FROM
			permission p1
		WHERE
			\"key\" = 'menu::ap_invoices' AND
			NOT EXISTS (
				SELECT
					1
				FROM
					permission p2
				WHERE
					p2.profile_id = p1.profile_id AND
					p2.\"key\" = 'ap::post_batches'
			)
	";
}

Database::query($query);
