<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_ap_invoice_line_item", "credit_memo")) {
	Database::createColumn("gl_ap_invoice_line_item", "credit_memo", "INT");
}

Database::commit();
return true;
?>