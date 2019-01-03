<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql =
	"UPDATE
		gl_wh_transaction
	SET
		type = 21
	FROM
		gl_wh_return_request_items i
	WHERE
		gl_wh_transaction.source = 'WarehouseReturnRequestItem' AND
		i.id = gl_wh_transaction.source_id AND 
		i.source = 'maintenance_request_item'";

Database::query($sql);
Database::commit();
return true;
?>