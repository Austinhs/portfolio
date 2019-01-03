<?php

if (Database::tableExists('email_notifications')) {
	$index_exists = Database::indexExists('email_notifications', 'email_notifications_index');

	// Must delete duplicates before creating index
	Database::query("DELETE FROM email_notifications WHERE id NOT IN( SELECT max(id) FROM email_notifications GROUP BY user_id, user_type)");

	// Only create index if it doesn't already exist
	if (!$index_exists) {
		Database::query("CREATE UNIQUE INDEX email_notifications_index ON email_notifications(user_id, user_type)");
	}
}