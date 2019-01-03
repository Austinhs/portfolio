<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql =
	"UPDATE
		ps_district_fee_templates_joins
	SET
		deleted = 1
	WHERE
		program_id NOT IN
			(
				SELECT
					id
				FROM
					ps_programs
			)";

Database::begin();
Database::query($sql);
Database::commit();
return true;
?>