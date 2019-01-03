<?php

Database::query("
	UPDATE
		permission
	SET
		\"key\" = 'package::Finance'
	WHERE
		\"key\" = 'package::Finance::ERP' AND
		NOT EXISTS(
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.\"key\" = 'package::Finance' AND
				p2.profile_id = permission.profile_id
		)
");
