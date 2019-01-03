<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$columns = AccountingStrip::$validModes;

foreach($columns as $column) {
	if(!Database::columnExists('gl_permission', $column)) {
		Database::createColumn('gl_permission', $column, 'bigint');
	}
}

$query = "
UPDATE
	gl_permission
SET
	ar = 1,
	ap = 1,
	fin = 1,
	wh = 1,
	fa = 1
WHERE
	type = 'accounting_strip_permissions'
";

Database::query($query);
?>
