<?php
	Database::query("
		UPDATE
			student_enrollment
		SET
			graduation_requirement_program = NULL
		WHERE
			graduation_requirement_program NOT IN (
				SELECT
					id
				FROM
					grad_subject_programs
			)
	");
?>