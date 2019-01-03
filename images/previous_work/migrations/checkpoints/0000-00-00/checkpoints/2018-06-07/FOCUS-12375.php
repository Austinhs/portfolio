<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql =
	"UPDATE
		gl_saved_report
	SET
		source = 'erpwhtransactionreportgeneral'
	WHERE
		source = 'CatalogReport' AND
		criteria LIKE '%transaction[%'";

Database::begin();
Database::query($sql);
Database::commit();
return true;
?>