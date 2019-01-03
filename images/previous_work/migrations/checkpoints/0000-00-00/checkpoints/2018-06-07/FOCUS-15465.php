<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("ps_fees")) {
	return false;
}

Database::begin();

if (!Database::columnExists("ps_fees", "deleted")) {
	Database::createColumn("ps_fees", "deleted", "INT");
}

Database::commit();
return true;
?>