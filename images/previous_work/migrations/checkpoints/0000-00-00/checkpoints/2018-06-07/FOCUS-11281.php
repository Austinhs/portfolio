<?php

if(empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_wh_types', 'internal_accounting_strip_hash')) {
	Database::createColumn('gl_wh_types', 'internal_accounting_strip_hash', 'text');
}
