<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_ap_request_line_item")) {
	return false;
}

Database::begin();
Database::changeColumnType("gl_ap_request_line_item", "amount", "NUMERIC", "(28,10)");
Database::changeColumnType("gl_ap_request_line_item", "price", "NUMERIC", "(28,10)");
Database::changeColumnType("gl_ap_request_line_item", "qty", "NUMERIC", "(28,10)");
Database::commit();

return true;
?>