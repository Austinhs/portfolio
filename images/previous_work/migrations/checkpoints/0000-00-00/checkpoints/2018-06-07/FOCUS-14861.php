<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pos_invoice", "accounting_type")) {
	Database::createColumn("gl_pos_invoice", "accounting_type", "CHAR");

	$sql =
		"UPDATE
			gl_pos_invoice
		SET
			accounting_type = 'R'";

	Database::query($sql);
}

Database::commit();
return true;
?>