<?php

if(empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_positions', 'last_modified_by')) {
	Database::createColumn('gl_pr_positions', 'last_modified_by', 'varchar', '255');
}

if(!Database::columnExists('gl_pr_positions', 'last_modified_date')) {
	Database::createColumn('gl_pr_positions', 'last_modified_date', 'timestamp');
}

if(!Database::columnExists('gl_pr_positions', 'last_modified_time')) {
	Database::createColumn('gl_pr_positions', 'last_modified_time', 'timestamp');
}
