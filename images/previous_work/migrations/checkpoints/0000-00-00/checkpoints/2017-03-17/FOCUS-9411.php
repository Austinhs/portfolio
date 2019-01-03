<?php

if (empty(Database::columnExists('custom_field_log_columns', 'rich_text'))){
	Database::createColumn('custom_field_log_columns', 'rich_text', 'smallint');
}