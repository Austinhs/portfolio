<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if(!Database::columnExists('gl_pr_run_controls', 'find_abort')) {
	Database::createColumn('gl_pr_run_controls', 'find_abort', 'integer');
}


if(!Database::columnExists('gl_pr_misc', 'run_id')) {
	Database::createColumn('gl_pr_misc', 'run_id', 'integer');
}

Database::commit();
