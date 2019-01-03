<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_pos_invoice_allocation", "course_periods")) {
	Database::begin();
	Database::createColumn("gl_pos_invoice_allocation", "course_periods", "TEXT");
	Database::commit();
}

return true;
?>