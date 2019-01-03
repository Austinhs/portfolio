<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}


if(!Database::columnExists(FacilityByYear::$table, 'rollover_id')) {
	Database::createColumn(FacilityByYear::$table, 'rollover_id', 'bigint');
}
