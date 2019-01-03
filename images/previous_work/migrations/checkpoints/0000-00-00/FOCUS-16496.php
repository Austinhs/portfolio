<?php
if(!Database::columnExists('calendar_events ', 'uid')) {
	Database::createColumn('calendar_events', 'uid', 'varchar');
}

$sql = "
	ALTER TABLE
		calendar_events
	ALTER COLUMN
		title
	{{postgres: type}}
		varchar(255)
";

Database::query(Database::preprocess($sql));