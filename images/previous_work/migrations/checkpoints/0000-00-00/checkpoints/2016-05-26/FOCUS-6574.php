<?php

// First, make sure all the options exist
$correct_options = [
	[
		'code'  => 'Student',
		'label' => 'Student'
	],
	[
		'code'  => 'Parents',
		'label' => 'Parents'
	],
	[
		'code'  => 'Both',
		'label' => 'Both'
	]
];

$field = SISStudent::getFieldByAlias('letter_log');

if(empty($field)) {
	return;
	throw new Exception("Letter Log is not set up correctly");
}

$field_id = $field['id'];

$columns = SISStudent::getLogColumns($field['id']);
$column  = null;

foreach($columns as $tmp_column) {
	if(strtolower($tmp_column['column_name']) === 'log_field1') {
		$column = $tmp_column;
		break;
	}
}

if(empty($column)) {
	throw new Exception("Letter Log -> Recipient is not set up correctly");
}

$column_id = $column['id'];

// Convert the 'Students' option to 'Student' if there is one
$sql = "
	UPDATE
		custom_field_select_options
	SET
		code = 'Student',
		label = 'Student'
	WHERE
		source_class = 'CustomFieldLogColumn' AND
		source_id = :column_id
";

$params = [
	'column_id' => $column_id
];

Database::query($sql, $params);

// Make sure the options aren't cached
SISStudent::clearCache();

$field_options = SISStudent::getSelectOptions($field['id']);
$options       = empty($field_options[$column_id]) ? [] : $field_options[$column_id];
$option_ids    = [];

foreach($options as $option) {
	$option_ids[$option['code']] = strval($option['value']);
}

foreach($correct_options as $option) {
	if(empty($option_ids[$option['code']])) {
		$new_option = new CustomFieldSelectOption();

		$new_option
			->setRecord($option)
			->setSourceClass('CustomFieldLogColumn')
			->setSourceId($column_id)
			->setMigrated(1)
			->persist();

		$option_ids[$option['code']] = strval($new_option->getId());
	}
}

// If there are any 'Students' values, they should be converted to 'Student'
$option_ids['Students'] = $option_ids['Student'];

// Migrate the log entries
$col = strtoupper($column['column_name']);

foreach($option_ids as $code => $id) {
	$sql = Database::preprocess("
		UPDATE
			custom_field_log_entries
		SET
			{$col} = :id
		WHERE
			field_id = :field_id AND
			{$col} IS NOT NULL AND
			NOT({{is_int:{$col}}}) AND
			{$col} = :code
	");

	$params = [
		'field_id' => $field_id,
		'id'       => strval($id),
		'code'     => strval($code)
	];

	Database::query($sql, $params);
}
