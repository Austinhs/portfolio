<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (Database::tableExists("gl_pos_outside_source_check")) {
	$sql =
		"UPDATE
			gl_pos_outside_source_check
		SET
			calendar_year = CAST(SUBSTRING(CAST(check_date AS VARCHAR), 1, 4) AS INT)
		WHERE
			check_date IS NOT NULL";

	Database::query($sql);
}

Database::commit();
return true;
?>