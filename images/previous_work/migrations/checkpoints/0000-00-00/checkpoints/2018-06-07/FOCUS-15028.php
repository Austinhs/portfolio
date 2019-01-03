<?php

if(!Database::tableExists('scheduler_priorities')) {
	Database::query("
		CREATE TABLE scheduler_priorities (
			id BIGINT NOT NULL PRIMARY KEY,
			grad_subject_id BIGINT NOT NULL,
			school_id BIGINT NOT NULL,
			sort_order BIGINT NOT NULL
		)
	");
}

if(!Database::sequenceExists('scheduler_priorities_seq')) {
	Database::createSequence('scheduler_priorities_seq');
}

if(!Database::indexExists('scheduler_priorities', 'scheduler_priorities_grad_subject_id')) {
	Database::query("CREATE INDEX scheduler_priorities_grad_subject_id ON scheduler_priorities (grad_subject_id)");
}

if(!Database::indexExists('scheduler_priorities', 'scheduler_priorities_school_id')) {
	Database::query("CREATE INDEX scheduler_priorities_school_id ON scheduler_priorities (school_id)");
}

Database::query("
	INSERT INTO permission (
		profile_id,
		\"key\"
	) (
	SELECT
		profile_id,
		'SIS:EditSchedulerPriorities'
	FROM
		permission p1
	WHERE
		\"key\" = 'Scheduling/Scheduler.php:can_edit' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission
			WHERE
				\"key\"    = 'SIS:EditSchedulerPriorities' AND
				profile_id = p1.profile_id
		)
	)
");

Database::query("
	INSERT INTO user_permission (
		user_id,
		\"key\"
	) (
	SELECT
		user_id,
		'SIS:EditSchedulerPriorities'
	FROM
		user_permission p1
	WHERE
		\"key\" = 'Scheduling/Scheduler.php:can_edit' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				user_permission
			WHERE
				\"key\" = 'SIS:EditSchedulerPriorities' AND
				user_id = p1.user_id
		)
	)
");
