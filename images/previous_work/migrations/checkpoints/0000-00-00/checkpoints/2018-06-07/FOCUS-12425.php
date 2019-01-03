<?php

if(Database::$type=='postgres') {
	// Delete dupes before making primary keys
	Database::query("DELETE
		FROM
			co_teacher_days
		WHERE
			EXISTS (
				SELECT
					1
				FROM
					co_teacher_days ctd
				WHERE
					ctd.course_period_id = co_teacher_days.course_period_id
					AND ctd.teacher_id = co_teacher_days.teacher_id
					AND ctd.ctid > co_teacher_days.ctid
			)"
	);
	// Make them primary keys
	Database::query("ALTER TABLE co_teacher_days ADD PRIMARY KEY (course_period_id,teacher_id)");

} else if (Database::$type=='mssql') {
	// Add a temp id column so each row has a unique id
	Database::query("
		ALTER TABLE
			co_teacher_days
		ADD
			temp_id integer identity
	");

	// Use the temp id to delete duplicates
	Database::query("
		DELETE FROM
			co_teacher_days
		WHERE
			EXISTS (
				SELECT
					1
				FROM
					co_teacher_days ctd
				WHERE
					ctd.course_period_id = co_teacher_days.course_period_id
					AND ctd.teacher_id = co_teacher_days.teacher_id
					AND ctd.temp_id > co_teacher_days.temp_id
	)");

	// Drop the temp id column we added
	Database::query("
		ALTER TABLE
			co_teacher_days
		DROP COLUMN
			temp_id
	");

}
