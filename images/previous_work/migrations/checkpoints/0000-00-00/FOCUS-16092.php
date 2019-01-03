<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Migrations::depend("FOCUS-15113");
Database::begin();

$sql =
	"UPDATE
		gl_pos_invoice_allocation
	SET
		accounting_strip_id = REPLACE(REPLACE(accounting_strip_id, '[', '{'), ']', '}')
	WHERE
		accounting_strip_id LIKE '[%' OR
		accounting_strip_id LIKE '%]'";

Database::query($sql);
Database::commit();
return true;
?>