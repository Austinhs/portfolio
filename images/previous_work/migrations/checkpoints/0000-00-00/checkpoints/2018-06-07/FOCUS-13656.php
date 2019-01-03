<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_fiscal_year", "internal_year_end")) {
	$timestamp = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";

	Database::createColumn("gl_fiscal_year", "internal_year_end", $timestamp);

	$sql =
		"UPDATE
			gl_fiscal_year
		SET
			internal_year_end = year_end";

	Database::query($sql);
}

Database::commit();
return true;
?>