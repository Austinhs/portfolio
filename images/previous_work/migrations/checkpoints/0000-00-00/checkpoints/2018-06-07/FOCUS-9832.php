<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_ap_invoice", "created_by")) {
	Database::begin();
	Database::createColumn("gl_ap_invoice", "created_by", "BIGINT");

	$sql = 
		"UPDATE
			gl_ap_invoice
		SET
			created_by = 
				(
					SELECT
						MAX(user_id)
					FROM
						database_object_log
					WHERE
						record_id = gl_ap_invoice.invoice_id AND
						record_class = 'Invoice' AND
						action = 'INSERT'
				)";

	Database::query($sql);
	Database::commit();
}

return true;
?>