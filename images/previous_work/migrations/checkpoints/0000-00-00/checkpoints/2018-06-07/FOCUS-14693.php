<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Migrations::depend("FOCUS-12316");

if(Database::tableExists('gl_hr_ess_info_chg_batch') && !Database::columnExists('gl_hr_ess_info_chg_batch', 'reason') ) {
	Database::createColumn('gl_hr_ess_info_chg_batch', 'reason', 'text');
}
