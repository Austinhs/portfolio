<?php

Migrations::depend('FOCUS-16577');

Database::query("
	UPDATE
		users
	SET
		username = 'cron'
	WHERE
		username = '__internal_focus_user__' AND
		NOT EXISTS(
			SELECT 1 FROM users WHERE username = 'cron'
		)
");

Database::query("
	DELETE FROM
		users
	WHERE
		username = '__internal_focus_user__'
");
