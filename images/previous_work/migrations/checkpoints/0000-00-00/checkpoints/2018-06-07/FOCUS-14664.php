<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql1 = 
	"INSERT INTO
		PERMISSION
		(id, profile_id, \"key\")
	SELECT
		{{next:permission_seq}},
		profile_id,
		'ap::can_edit_terms'
	FROM
		PERMISSION p1
	WHERE
		\"key\" = 'ap::edit_requests'
		AND NOT EXISTS(
			SELECT 
			* 
			FROM PERMISSION p2
			WHERE
			\"key\" = 'ap::can_edit_terms' 
				AND p1.profile_id = p2.profile_id
		)
	";

$sql = Database::preprocess($sql1);

Database::query($sql);

$sql2 = 
	"INSERT INTO
		PERMISSION
		(id, profile_id, \"key\")
	SELECT
		{{next:permission_seq}},
		profile_id,
		'ap::ia_can_edit_terms'
	FROM
		PERMISSION p1
	WHERE
		\"key\" = 'ap::ia_edit_requests'
		AND NOT EXISTS(
			SELECT 
			* 
			FROM PERMISSION p2
			WHERE
			\"key\" = 'ap::ia_can_edit_terms' 
				AND p1.profile_id = p2.profile_id
		)
	";

$sql = Database::preprocess($sql2);

Database::query($sql);

return true;
?>