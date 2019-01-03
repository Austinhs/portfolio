<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (Database::tableExists("gl_ap_invoice_allocation")) {
	if (!Database::columnExists("gl_ap_invoice_allocation", "request_line_invoice_qty")) {
		Database::createColumn("gl_ap_invoice_allocation", "request_line_invoice_qty", "NUMERIC", "(28,10)");
	}

	if (!Database::columnExists("gl_ap_invoice_allocation", "request_line_item_id")) {
		Database::createColumn("gl_ap_invoice_allocation", "request_line_item_id", "BIGINT");
	}
}

if (Database::tableExists("gl_ap_invoice_line_item")) {
	if (!Database::columnExists("gl_ap_invoice_line_item", "invoice_allocation_id")) {
		Database::createColumn("gl_ap_invoice_line_item", "invoice_allocation_id", "BIGINT");
	}
}

if (!Database::tableExists("gl_ap_receiving_invoiced")) {
	$sql =
		"CREATE TABLE gl_ap_receiving_invoiced (
			id BIGINT PRIMARY KEY,
			deleted INT,
			invoice_id BIGINT,
			invoice_line_item_id BIGINT,
			request_line_item_id BIGINT,
			receiving_id BIGINT,
			quantity NUMERIC(28, 10)
		)";

	Database::query($sql);
}

Database::commit();
return true;
?>