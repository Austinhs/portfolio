<?php

$sql =
	"INSERT INTO
		permission
			(id, profile_id, \"key\")
	SELECT
		{{next:permission_seq}},
		p.profile_id,
		'ar::edit_pos'
	FROM
		permission p
	WHERE
		p.\"key\" LIKE '%menu::ar_pos%' AND
		NOT EXISTS
			(
				SELECT
					1
				FROM
					permission p2
				WHERE
					p2.profile_id = p.profile_id AND
					p2.\"key\" IN ('ar::locked_pos', 'ar::edit_pos')
			)";
$sql = Database::preprocess($sql);

Database::query($sql);
