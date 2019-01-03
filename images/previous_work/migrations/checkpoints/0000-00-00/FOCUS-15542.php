<?php
$sql = "
	INSERT INTO
		program_config
			(
				program,
				title,
				value
			)
	SELECT
			'Preferences',
			'RECIEVE_FORMBUILDER_APPROVALFLOW_NOTIFICATIONS',
			'Y'
	WHERE NOT EXISTS
		(
			SELECT
				*
			FROM
				program_config
			WHERE
				title = 'RECIEVE_FORMBUILDER_APPROVALFLOW_NOTIFICATIONS'
		);
";

Database::query($sql);