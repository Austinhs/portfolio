<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return;
}

Database::query("
	DELETE FROM sss_progress_updates
	WHERE goal_id IN (
		SELECT 
			g.id 
		FROM 
			sss_goals g 
			JOIN sss_event_instances e ON (g.event_instance_id = e.id AND g.student_id != e.student_id)
	)
");

Database::query("
	DELETE FROM sss_objectives
	WHERE goal_id IN (
		SELECT 
			g.id 
		FROM 
			sss_goals g 
			JOIN sss_event_instances e ON (g.event_instance_id = e.id AND g.student_id != e.student_id)
	)
");
