<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_pos_invoice")) {
	return false;
}

if (!Database::columnExists("gl_pos_invoice", "customer_comments")) {
	Database::createColumn("gl_pos_invoice", "customer_comments", "TEXT");
}

return true;