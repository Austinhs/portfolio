<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"])
	return false;

Database::begin();
Database::query("UPDATE gl_ba_checks SET module = 'ap' WHERE module = 'ap_checks'");
Database::commit();

return true;
