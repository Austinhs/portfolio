<?php
Database::query("
	INSERT INTO program_config (syear, program, title, value) (
		SELECT
			syear,
			program,
			'CC_COURSE_NUM_LENGTH',
			value
		FROM
			program_config
		WHERE
			title = 'COURSE_NUM_LENGTH'
			AND program = 'system'
			AND NOT EXISTS(
				SELECT
					1
				FROM
					program_config
				WHERE
					title = 'CC_COURSE_NUM_LENGTH'
					AND program = 'system'
			)
	)
");
