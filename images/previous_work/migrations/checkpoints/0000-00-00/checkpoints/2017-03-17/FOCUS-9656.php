<?php
if(!purchasedCTE() || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Migrations::depend('FOCUS-6398');

if(!Database::columnExists(Ps1098THistory::$table, 'school_id')) {
	Database::createColumn(Ps1098THistory::$table, 'school_id', 'bigint');
}

if(!Database::columnExists(Ps1098THistory::$table, 'facility_id')) {
	Database::createColumn(Ps1098THistory::$table, 'facility_id', 'bigint');
}

?>
