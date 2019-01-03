<?php

if (Database::$type == "mssql") {
	if (Database::columnExists('lesson_plan', 'last_updated')) {
		Database::query("ALTER TABLE lesson_plan DROP COLUMN last_updated");
		Database::createColumn('lesson_plan', 'last_updated', 'datetime', null, true);
	}

	if (Database::columnExists('forum', 'due_date')) {
		Database::query("ALTER TABLE forum DROP COLUMN due_date");
		Database::createColumn('forum', 'due_date', 'datetime', null, true);
	}
}
