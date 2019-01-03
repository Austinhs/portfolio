<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$setting = Settings::get("revenue_transfers");

if (!$setting) {
	return true;
}

$sql = 
	"INSERT INTO
		PERMISSION
		(id, profile_id, \"key\")
	SELECT
		{{next:permission_seq}},
		profile_id,
		'bm::allow_revenue_transfers'
	FROM
		PERMISSION
	WHERE
		\"key\" = 'menu::gl_budget_maintenance'
	";

$sql = Database::preprocess($sql);

Database::query($sql);
return true;
?>