<?php
// Delete all school_choice_application_status records that are tied to students that no longer exist.
Database::query("
	DELETE FROM
		school_choice_application_status
	WHERE
		student_id
	IN (
		SELECT
			status.student_id
		FROM
			school_choice_application_status
		AS
			status
		LEFT JOIN
			students
		ON
			status.student_id = students.student_id
		WHERE
			students.student_id is null)
");
