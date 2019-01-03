<?php

$sql = "
	DELETE FROM
		program_user_config
	WHERE
		program='StudentFieldsView' AND
		title IN (
			SELECT
				CAST(id as VARCHAR)
			FROM
				custom_fields
			WHERE
				deleted='1'
		)
";

Database::query($sql);

$sql = "
	DELETE FROM
		program_user_config
	WHERE
		program = 'StudentFieldsView' AND
		title IN (
			SELECT
				CAST(id AS VARCHAR)
			FROM
				custom_fields
			WHERE
				type = 'log'
		);";

Database::query($sql);