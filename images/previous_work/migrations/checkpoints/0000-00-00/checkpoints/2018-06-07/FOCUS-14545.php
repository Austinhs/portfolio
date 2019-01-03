<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

//Update title for DOE change
Database::query("UPDATE CUSTOM_FIELDS SET TITLE = 'Qualified Paraprofessional' WHERE ALIAS = 'highly_qualified_paraprofessional'");

Database::commit();
