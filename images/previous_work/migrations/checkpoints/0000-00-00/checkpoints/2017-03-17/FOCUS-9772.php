<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_address")) {
	return false;
}

if (!Database::columnExists("gl_address", "primary_addr")) {
	Database::begin();
	Database::createColumn("gl_address", "primary_addr", "BIGINT");
	Database::commit();
}

$sql =
	"UPDATE
		gl_address
	SET
		primary_addr = 1
	WHERE
		COALESCE(primary_addr, 0) = 0 AND
		NOT EXISTS
			(
				SELECT
					1
				FROM
					gl_address a2
				WHERE
					a2.parent_id = gl_address.parent_id AND
					a2.primary_addr = 1
			) AND
		id =
			(
				SELECT
					MAX(id)
				FROM
					gl_address a2
				WHERE
					a2.parent_id = gl_address.parent_id
			)";

Database::begin();
Database::query($sql);
Database::commit();
?>