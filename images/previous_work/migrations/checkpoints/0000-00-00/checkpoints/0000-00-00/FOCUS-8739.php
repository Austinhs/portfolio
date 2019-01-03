<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::begin();

$type = 'numeric';
if(Database::$type === 'mssql') {
	$type = 'float';
}

Database::changeColumnType('gl_ap_request_line_item', 'qty_released', $type);

$sql = "
	UPDATE
		gl_ap_request_line_item
	SET
		qty_released = qty
	WHERE
		qty_released IS NULL AND
		COALESCE(released_for_payment, 0) = 1
";
Database::query($sql);

Database::commit();
