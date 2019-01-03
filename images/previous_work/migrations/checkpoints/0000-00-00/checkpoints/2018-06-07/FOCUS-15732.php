<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_element_use_restrictions", "updated_at")) {
	Database::createColumn("gl_element_use_restrictions", "updated_at", "TIMESTAMP");

	$latest_time = date("Y-m-d H:i:s");
	$params      = [
		"latest_time" => $latest_time
	];
	$sql         =
		"UPDATE
			gl_element_use_restrictions
		SET
			updated_at = :latest_time";

	Database::query($sql, $params);
}

Database::commit();
return true;
?>