<?php

/**
 * Quick migration to make sure all
 * outstanding reqs are truly outstanding
 */

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::begin();

if(!Database::columnExists('gl_ap_approval_record', 'sub')) {
	Database::createColumn('gl_ap_approval_record', 'sub', 'bigint');
}

Database::commit();
