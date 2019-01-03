<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

Migrations::depend("FOCUS-15113");

if (!Database::tableExists("gl_pos_invoice_allocation_strip")) {
	return false;
}

Database::begin();

if (!Database::indexExists("gl_pos_invoice_allocation_strip", "gl_pos_invoice_allocation_strip__invoice_allocation_id")) {
	$sql =
		"CREATE INDEX
			gl_pos_invoice_allocation_strip__invoice_allocation_id
		ON
			gl_pos_invoice_allocation_strip
				(invoice_allocation_id)";

	Database::query($sql);
}

if (!Database::indexExists("gl_pos_invoice_allocation_strip", "gl_pos_invoice_allocation_strip__accounting_strip_id")) {
	$sql =
		"CREATE INDEX
			gl_pos_invoice_allocation_strip__accounting_strip_id
		ON
			gl_pos_invoice_allocation_strip
				(accounting_strip_id)";

	Database::query($sql);
}

Database::commit();
return true;
?>