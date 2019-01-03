<?php

if(!$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::begin();

if(!Database::columnExists('gl_pr_positions', 'manager_id')) {
	Database::createColumn('gl_pr_positions', 'manager_id', 'bigint');
}

if(!Database::columnExists('gl_pr_positions', 'manager')) {
	Database::createColumn('gl_pr_positions', 'manager', 'bigint');
}

Database::commit();
