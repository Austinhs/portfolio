<?php

Database::query("
	INSERT INTO
		program_config
		(
			syear,
			school_id,
			program,
			title,
			value
		)
	SELECT
		*
	FROM
		(
			SELECT
				syear,
				school_id,
				program,
				'DEFAULT_GRAD_REQS_REPORT_TERM' AS title,
				'COURSE_HISTORY' AS value
			FROM
				program_config
			WHERE
				title = 'WEIGHT_GPA_BY_CREDITS' AND
				value = 'Y'
		) tmp
	WHERE
		NOT EXISTS (
			SELECT
				NULL
			FROM
				program_config existing
			WHERE
				existing.syear                  = tmp.syear AND
				COALESCE(existing.school_id, 0) = COALESCE(tmp.school_id, 0) AND
				existing.program                = CAST(tmp.program AS VARCHAR) AND
				existing.title                  = CAST(tmp.title AS VARCHAR)
		)
");
