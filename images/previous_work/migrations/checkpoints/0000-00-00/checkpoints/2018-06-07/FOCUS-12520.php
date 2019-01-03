<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql =
	"UPDATE
		gl_permission_value
	SET
		value =
			CASE
				WHEN
					value = 'maintenance_request'
				THEN
					'maintenance_request_item'
				WHEN
					value = 'request'
				THEN
					'request_line_item'
				ELSE
					value
			END
	WHERE
		field = 'source' AND
		permission_id IN
			(
				SELECT
					id
				FROM
					gl_permission
				WHERE
					approval_type = 'WarehouseReturnRequest'
			)";

Database::query($sql);
Database::commit();
return true;
?>