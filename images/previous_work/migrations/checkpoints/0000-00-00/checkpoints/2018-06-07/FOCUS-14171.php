<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql    =
	"INSERT INTO
		permission
	SELECT
		{{next:permission_seq}},
		profile_id,
		'fa::allow_inventory_files_comments'
	FROM
		permission
	WHERE
		\"key\" = :key";
$params = [
	"key" => "menu::fa_take_inventory"
];

Database::query(Database::preprocess($sql), $params);
Database::commit();
return true;
?>