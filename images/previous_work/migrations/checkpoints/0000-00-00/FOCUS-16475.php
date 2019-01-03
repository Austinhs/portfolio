<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (Database::$type === 'mssql') {
	$type = 'datetime2';
} else {
	$type = 'timestamp';
}

Database::begin();

if (!Database::columnExists("gl_pr_journal_reallocations", "new_journal")) {
	Database::createColumn("gl_pr_journal_reallocations", "new_journal", "INT");
}

if (!Database::columnExists("gl_pr_journal_reallocations", "journal_date")) {
	Database::createColumn("gl_pr_journal_reallocations", "journal_date", $type);
}

if (!Database::columnExists("gl_ba_checks", "voided_journal_date")) {
	Database::createColumn("gl_ba_checks", "voided_journal_date", $type);
}

Database::commit();
return true;
?>
