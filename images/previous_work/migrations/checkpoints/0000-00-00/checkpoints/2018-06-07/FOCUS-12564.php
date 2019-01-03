<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if(!Database::columnExists('gl_address', 'mailing_1099')) {
	Database::createColumn('gl_address', 'mailing_1099', 'BIGINT');
}

Database::commit();

return true;
