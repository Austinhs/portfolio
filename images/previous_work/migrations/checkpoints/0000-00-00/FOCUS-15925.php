<?php

	$classes = [
		'SISStudent',
		'FocusUser',
	];

	$query = "
		SELECT
			id
		FROM
			custom_fields
		WHERE
			alias = 'terms_and_conditions' AND
			source_class = :class
	";

	$records = [
		[
			'column_name' => 'LOG_FIELD1',
			'type'        => 'date',
			'title'       => 'Date',
		],
		[
			'column_name' => 'LOG_FIELD2',
			'type'        => 'text',
			'title'       => 'Name',
		],
		[
			'column_name' => 'LOG_FIELD3',
			'type'        => 'checkbox',
			'title'       => 'Confirmation',
		]
	];

	foreach($classes as $class) {
		$exists = Database::get($query, ['class' => $class]);

		if(empty($exists)) {
			$custom_field = new CustomField();

			$custom_field
				->setSourceClass($class)
				->setType('log')
				->setTitle('Terms and Conditions')
				->setAlias('terms_and_conditions')
				->setSystem(1)
				->persist();

			$field_id = $custom_field->getId();
		}

		$field_id = empty($exists) ? $field_id : current($exists)['ID'];

		foreach($records as $record) {
			$sql = "
				SELECT
					1
				FROM
					custom_field_log_columns
				WHERE
					field_id = {$field_id} AND
					column_name = '{$record['column_name']}'
			";

			$exists = Database::get($sql);

			if(empty($exists)) {
				$log_column = new CustomFieldLogColumn();

				$log_column
					->setFieldId($field_id)
					->setColumnName($record['column_name'])
					->setType($record['type'])
					->setTitle($record['title'])
					->persist();
			}
		}
	}