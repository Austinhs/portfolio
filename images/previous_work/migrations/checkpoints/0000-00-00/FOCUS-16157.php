<?php


if(!Database::columnExists('custom_fields', 'visible_syear_column')) {
	Database::createColumn('custom_fields', 'visible_syear_column', 'bigint');
}

if(!empty($GLOBALS['_FOCUS']['config']['state_name'])) {
	$state_name = strtolower($GLOBALS['_FOCUS']['config']['state_name']);
}
if($state_name !== 'florida') {
	return;
}

$field = SISStudent::getFieldByColumnName('custom_200000237');
if(is_null($field)) {
	return;
}

$field_id = $field['id'];

// update syear column with school year column value
Database::query("
	UPDATE
		custom_field_log_entries
	SET
		syear = CAST(cfso.code AS int)
	FROM
		custom_field_select_options cfso
	WHERE
		CAST(cfso.id AS varchar) = CAST(custom_field_log_entries.log_field1 AS varchar)
		AND custom_field_log_entries.field_id = '{$field_id}'
		AND custom_field_log_entries.syear is null
");

//turn on visible syear option
Database::query("
	UPDATE
		custom_fields
	SET
		visible_syear_column = 1
	WHERE
		id = '{$field_id}'
");

// remove previous school year column
Database::query("
	DELETE FROM
		custom_field_log_columns
	WHERE
		field_id = '{$field_id}'
		AND column_name = 'LOG_FIELD1'
");

