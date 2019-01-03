<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (Database::columnExists("gl_manual_journal_validation", "application_level")) {
	return true;
}

Database::begin();
Database::createColumn("gl_manual_journal_validation", "application_level", "CHAR");

$sql =
	"UPDATE
		gl_manual_journal_validation
	SET
		application_level = 'B'";

Database::query($sql);
Database::commit();
return true;
?>