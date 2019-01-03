<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql =
	"INSERT INTO
		permission
			(id, \"key\", profile_id)
	SELECT
		{{next:permission_seq}},
		'ap::edit_vendor_contact_info',
		profile_id
	FROM
		permission
	WHERE
		\"key\" = 'ap::edit_vendor_address_contact_info'";
$sql = Database::preprocess($sql);

Database::query($sql);

$sql =
	"INSERT INTO
		permission
			(id, \"key\", profile_id)
	SELECT
		{{next:permission_seq}},
		'ap::edit_vendor_payment_addresses',
		profile_id
	FROM
		permission
	WHERE
		\"key\" = 'ap::edit_vendor_address_contact_info'";
$sql = Database::preprocess($sql);

Database::query($sql);

$sql =
	"INSERT INTO
		permission
			(id, \"key\", profile_id)
	SELECT
		{{next:permission_seq}},
		'ap::edit_vendor_other_addresses',
		profile_id
	FROM
		permission
	WHERE
		\"key\" = 'ap::edit_vendor_address_contact_info'";
$sql = Database::preprocess($sql);

Database::query($sql);
Database::commit();
return true;
?>