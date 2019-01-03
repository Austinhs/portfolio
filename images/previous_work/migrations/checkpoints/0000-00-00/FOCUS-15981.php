<?php

$parentSQL = "
	UPDATE
		program_config
	SET
		title = 'USE_SAML_PARENT',
		value = CASE
					WHEN pc_old.value = 'N'
						THEN 'Y'
						ELSE 'N'
				END
	FROM (
		SELECT
			value,
			syear,
			school_id,
			program
		FROM
			program_config
		WHERE
			title = 'SKIP_SAML_PARENT'
	) AS pc_old
	WHERE
		title = 'SKIP_SAML_PARENT'
	AND program_config.syear = pc_old.syear
	AND (
			program_config.school_id = pc_old.school_id
		OR (
				program_config.school_id IS NULL
			AND pc_old.school_id IS NULL
		)
	)
	AND program_config.program = pc_old.program
	AND NOT EXISTS (
		SELECT
			NULL
		FROM
			program_config
		WHERE
			syear = pc_old.syear
			AND school_id = pc_old.school_id
			AND program = pc_old.program
			AND title = 'USE_SAML_PARENT'
			AND (
					school_id = pc_old.school_id
				OR (
						school_id IS NULL
					AND pc_old.school_id IS NULL
				)
			)
		)";

$studentSQL = "
	UPDATE
		program_config
	SET
		title = 'USE_SAML_STUDENT',
		value = CASE
					WHEN pc_old.value = 'N'
						THEN 'Y'
						ELSE 'N'
				END
	FROM (
		SELECT
			value,
			syear,
			school_id,
			program
		FROM
			program_config
		WHERE
			title = 'SKIP_SAML_STUDENT'
	) AS pc_old
	WHERE
		title = 'SKIP_SAML_STUDENT'
		AND program_config.syear = pc_old.syear
		AND (
				program_config.school_id = pc_old.school_id
			OR (
					program_config.school_id IS NULL
				AND pc_old.school_id IS NULL
			)
		)
		AND program_config.program = pc_old.program
		AND NOT EXISTS (
			SELECT
				NULL
			FROM
				program_config
			WHERE
				syear = pc_old.syear
				AND program = pc_old.program
				AND title = 'USE_SAML_STUDENT'
				AND (
						school_id = pc_old.school_id
					OR (
							school_id IS NULL
						AND pc_old.school_id IS NULL
					)
				)
			)";

$deleteSQL = "
	DELETE FROM
		program_config
	WHERE
		title = 'SKIP_SAML_PARENT'
	OR title = 'SKIP_SAML_STUDENT'";

Database::query($parentSQL);
Database::query($studentSQL);
Database::query($deleteSQL);
