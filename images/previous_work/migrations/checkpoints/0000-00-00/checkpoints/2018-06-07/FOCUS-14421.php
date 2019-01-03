<?php

$data_types = [
		'text'             => 'TEXT',
		'textarea'         => 'TEXT',
		'numeric'          => 'NUMERIC',
		'date'             => 'TIMESTAMP',
		'time'             => 'VARCHAR',
		'signature'        => 'VARCHAR',
		'select'           => 'BIGINT',
		'select_one'       => 'BIGINT',
		'multiple'         => 'TEXT',
		'checkbox'         => 'CHAR',
	];

//gather all of the Student form fields
$fields = Database::get("
	SELECT
		cfc.id AS category_id,
		cf.id AS custom_field_id,
		cf.column_name,
		cf.type
	FROM
		custom_field_categories cfc
		JOIN custom_fields_join_categories cfjc ON (cfjc.category_id = cfc.id)
		JOIN custom_fields cf ON (cfjc.field_id = cf.id)
	WHERE
		cfc.form = 1
		and cf.source_class = 'SISStudent'");

foreach ($fields as $field) {
	$column_name = $field['COLUMN_NAME'];
	$data_type   = $data_types[$field['TYPE']];

	if(!empty($data_type)) {
		// Create the column if it doesn't exist
		if(!Database::columnExists('students_form_records', $column_name)) {
			Database::createColumn('students_form_records', $column_name, $data_type);
		}
	}
}
