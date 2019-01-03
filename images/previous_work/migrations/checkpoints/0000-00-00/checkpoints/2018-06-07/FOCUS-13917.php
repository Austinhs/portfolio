<?php
	if (!Database::columnExists("automated_cron_emails", "query_message")) {
		Database::createColumn("automated_cron_emails", "query_message", "TEXT");
	}

	//move all messages that are marked as query to the newly created column
	Database::query("
		UPDATE
			automated_cron_emails
		SET
			query_message = message,
			message = '',
			message_is_query = 0
		WHERE
			query_message is null
			AND message_is_query > 0");

?>