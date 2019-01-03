<?php
	if(!Database::columnExists('course_periods', 'active')) {
		$query = "
			ALTER TABLE
				course_periods
			ADD
				active VARCHAR(1) DEFAULT 'Y'
		";

		Database::query($query);

		$sql = "
			UPDATE
				course_periods
			SET
				active = 'Y'
		";

		Database::query($sql);
	}