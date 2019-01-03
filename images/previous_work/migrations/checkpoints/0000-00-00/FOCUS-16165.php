<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_wh_items", "accounting_type")) {
	Database::createColumn("gl_wh_items", "accounting_type", "CHAR", 1);

	$sql =
		"UPDATE
			gl_wh_items
		SET
			accounting_type = s.type
		FROM
			gl_wh_pools p,
			gl_accounting_strip s
		WHERE
			(
				(
					p.id = gl_wh_items.pool_id AND
					p.pos_active = 1
				) OR
				COALESCE(p.id, 0) = 0
			) AND
			s.id = gl_wh_items.accounting_strip_id";

	Database::query($sql);
}

Database::commit();
return true;
?>