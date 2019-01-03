<?php

// Tags: SSS
Migrations::depend('FOCUS-15908');

if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return;
}

// Delete goals that were incorrectly attributed to a different student
Database::query("
	DELETE FROM sss_goals
	WHERE student_id != (
		SELECT student_id
		FROM sss_event_instances
		WHERE sss_event_instances.id = sss_goals.event_instance_id
	)
");
