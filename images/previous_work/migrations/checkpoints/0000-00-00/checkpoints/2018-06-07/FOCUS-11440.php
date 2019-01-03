<?php
if(!Database::columnExists('users', 'last_updated_date')) {
	Database::createColumn('users', 'last_updated_date', 'DATE');
}

Database::query("
INSERT INTO
	program_confiG
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
			'USERS_PASSWORDS_EXPR' AS title,
			value
		FROM
			program_confiG
		WHERE
			title = 'PASSWORDS_EXPR'

		UNION ALL

		SELECT
			syear,
			school_id,
			program,
			'STUDENTS_AND_PARENTS_PASSWORDS_EXPR' AS title,
			value
		FROM
			program_confiG
		WHERE
			title = 'PASSWORDS_EXPR'
	) tmp
");

Database::query("
	DELETE FROM
		program_confiG
	WHERE
		title = 'PASSWORDS_EXPR'
");