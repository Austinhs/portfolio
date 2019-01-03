<?php

// Correct the app_type of School Choice application statuses.
$sql = "
	UPDATE
		school_choice_application_status {{postgres:scas}}
	SET
		app_type = (
			CASE WHEN (
				SELECT 
					count(*)
				FROM
					school_choice_application_status scas2
				JOIN
					school_choice_programs scp
				ON
					scas2.applying_program_id = scp.id
				JOIN
					school_choice_program_categories scpc
				ON
					scp.category = scpc.id
				WHERE
					scas2.id = scas.id) = 0
			THEN
				scas.app_type
			WHEN (
				SELECT
					scpc.spa
				FROM
					school_choice_application_status scas2
				JOIN
					school_choice_programs scp
				ON
					scas2.applying_program_id = scp.id
				JOIN
					school_choice_program_categories scpc
				ON
					scp.category = scpc.id
				WHERE
					scas2.id = scas.id
			) = 'Y'
			THEN
				'SPA'
			ELSE
				'Magnet'
			END
		)
{{mssql:FROM school_choice_application_status scas}}";

$sql = Database::preprocess($sql);

Database::query($sql);
