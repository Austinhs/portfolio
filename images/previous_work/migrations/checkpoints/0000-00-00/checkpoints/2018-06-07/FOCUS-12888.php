<?php

if(empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_banks', 'bank_csv')) {
	Database::createColumn('gl_banks', 'bank_csv', 'varchar');
}
