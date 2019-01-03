<?php

Migrations::depend('FOCUS-13336');
Migrations::depend('FOCUS-14874');

// Delete incorrect permissions that got added in the 8.0 migration
$sql = "
	DELETE FROM
		permission
	WHERE
		\"key\" LIKE 'CustomFields/%.php:can_%'
";

Database::query($sql);

// Make sure the users.custom_200000001 field has an alias of
// 'local_id', because it was added after most people had migrated
FocusUser::clearCache();

$field = FocusUser::getFieldByColumnName('custom_200000001');

if(!empty($field) && $field['alias'] !== 'local_id') {
	$field_obj = new CustomField($field['id']);

	$field_obj
		->setAlias('local_id')
		->persist();
}
