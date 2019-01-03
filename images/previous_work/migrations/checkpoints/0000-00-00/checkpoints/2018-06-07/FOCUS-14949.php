<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists('gl_ba_checks', 'void_reason')) {
	Database::createColumn('gl_ba_checks', 'void_reason', 'varchar');
}
