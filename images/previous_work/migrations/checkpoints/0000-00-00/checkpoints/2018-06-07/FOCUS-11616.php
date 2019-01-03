<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_dealer", "updated_at")) {
	Database::begin();
	Database::createColumn("gl_dealer", "updated_at", "TIMESTAMP");

	$sql = 
		"UPDATE
			gl_dealer
		SET
			updated_at = 
				(
					SELECT
						MAX(l.log_time)
					FROM
						gl_dealer d
					JOIN
						database_object_log l
					ON
						l.record_id = d.id AND
						l.record_class IN ('Dealer', 'Vendor', 'Customer')
					WHERE
						d.id = gl_dealer.id
				)";

	Database::query($sql);
	Database::commit();
}

return true;
?>