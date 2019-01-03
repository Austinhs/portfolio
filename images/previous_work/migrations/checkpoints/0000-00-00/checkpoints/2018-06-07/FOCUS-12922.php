<?php
// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

Migrations::depend('FOCUS-10794');

// Attempt to fix program_id on events that are null
Database::query("
	UPDATE sss_events SET program_id = (SELECT id FROM sss_programs sp WHERE sp.short_name = sss_events.category)
	WHERE program_id IS NULL;
");

// If category does not match program short name, they need to manually do this
// via SSS -> Setup -> Events tab -> Category dropdown
$bad_events = Database::get("SELECT name FROM sss_events WHERE program_id IS NULL");
if (!empty($bad_events)) {
	$bad_events = '"' . implode('", "', array_column($bad_events, 'NAME')) . '"';
	throw new Exception("FOCUS-12922 cannot be applied until you provide a category for the following SSS Events: {$bad_events}");
}

Database::query("UPDATE sss_event_instances SET program_id = (
		SELECT program_id FROM sss_events e WHERE e.id = sss_event_instances.event_id
	)
	WHERE program_id IS NULL
");
