<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_hr_ess_info_chg_deposits', 'bank_name')) {
	Database::createColumn('gl_hr_ess_info_chg_deposits', 'bank_name', 'varchar', '255');
}

if(!Database::columnExists('gl_hr_ess_info_chg_deposits', 'bank_id')) {
	Database::createColumn('gl_hr_ess_info_chg_deposits', 'bank_id', 'BIGINT');
}

if(!Database::columnExists('gl_hr_ess_info_chg_deposits', 'bank_code')) {
	Database::createColumn('gl_hr_ess_info_chg_deposits', 'bank_code', 'varchar', '255');
}
