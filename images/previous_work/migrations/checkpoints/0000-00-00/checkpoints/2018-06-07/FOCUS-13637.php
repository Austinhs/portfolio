<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_journals")) {
	return false;
}

Database::begin();

if (!Database::indexExists("gl_journals", "gl_journals_journal_date")) {
	$sql =
		"CREATE INDEX
			gl_journals_journal_date
		ON
			gl_journals (journal_date)";
	
	Database::query($sql);
}

Database::commit();
return true;
?>