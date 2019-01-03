<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (Database::tableExists("ps_billing_invoice_history")) {
	Database::changeColumnType("ps_billing_invoice_history", "bill_by", "VARCHAR", 255);
}

Database::commit();
return true;
?>