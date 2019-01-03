<?php

$text_type = Database::$type === 'mssql' ? 'varchar(max)' : 'text';

if (!Database::columnExists('users', 'session_persist_data')) {
	Database::createColumn('users', 'session_persist_data', $text_type);
}
