<?php 

if (!Database::columnExists('schedule', 'est_completion_date')) {
	Database::createColumn('schedule', 'est_completion_date', 'VARCHAR', null, $nullable = true);
}
