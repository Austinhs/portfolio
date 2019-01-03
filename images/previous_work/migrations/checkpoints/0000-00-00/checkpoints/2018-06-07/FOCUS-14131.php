<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_tx_strip_crosswalk")) {
	return false;
}

Database::begin();
Database::changeColumnType("gl_tx_strip_crosswalk", "crosswalk_to", "VARCHAR", 4);
Database::commit();
return true;
?>