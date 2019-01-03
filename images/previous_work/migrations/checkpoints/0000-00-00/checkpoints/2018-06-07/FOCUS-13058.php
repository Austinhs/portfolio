<?php
	Database::query("
		INSERT INTO permission (
			profile_id,
			\"key\"
		)
		SELECT
			profile_id,
			'SIS:ReportCardTemplateSettings' AS \"key\"
		FROM
			permission
		WHERE
			(
				\"key\" = 'Grades/ReportCards.php:can_edit' OR
				\"key\" = 'Grades/ReportCards.php:can_view'
			)
			AND
			NOT EXISTS (
				SELECT
					NULL
				FROM
					permission tmp
				WHERE
					tmp.profile_id = permission.profile_id AND
					tmp.\"key\" = 'SIS:ReportCardTemplateSettings'
			)
		GROUP BY
			permission.profile_id
	");