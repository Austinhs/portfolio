<?php

Migrations::depend('FOCUS-11459');
Migrations::depend('FOCUS-6710');
Migrations::depend('FOCUS-5468');
Migrations::depend('FOCUS-6359');

// Find the field you're looking for by legacy ID
$where = [
	"source_class = :class",
	"legacy_id = :legacy_id"
];

$params = [
	'class'     => 'SISUser',
	'legacy_id' => 20120005
];

$field = CustomField::getOne($where, null, $params);

if(empty($field)) {
	return;
	throw new Exception("This migration cannot continue because Teacher Certifications logging field is not set up.");
}

// Assign an alias and set it as a system field
$field
	->setAlias('teacher_certifications')
	->setSystem(1)
	->persist();

CustomFieldObject::clearCache();

// Get the existing field definition for the teacher certifications field
$field = SISUser::getFieldByAlias('teacher_certifications');

// Add the Teacher Certifications -> Scope log column
$title = "Scope";

$options = [
	[
		'code'  => 'A',
		'label' => '[A] Adjunct'
	],
	[
		'code'  => 'E',
		'label' => '[E] Exam (NC)'
	],
	[
		'code'  => 'G',
		'label' => '[G] Grandfathered'
	],
	[
		'code'  => 'H',
		'label' => '[H] HOUSSE'
	],
	[
		'code'  => 'S',
		'label' => '[S] Substitute with RG certificate'
	],
	[
		'code'  => 'O',
		'label' => '[O] Board Approved'
	],
	[
		'code'  => 'P',
		'label' => '[P] Pending'
	],
	[
		'code'  => 'L',
		'label' => '[L] State License'
	],
	[
		'code'  => 'Z',
		'label' => '[Z] Substitute 6077'
	],
	[
		'code'  => 'N',
		'label' => '[N]'
	],
	[
		'code'  => 'X',
		'label' => '[X]'
	],
	[
		'code'  => 'V',
		'label' => '[V] Virtual Teacher'
	]
];

// Get the existing columns for the field
$columns = SISUser::getLogColumns($field['id']);
$exists  = false;
$col_i   = 0;
$sort_i  = 0;

foreach($columns as $column) {
	if($column['title'] === $title) {
		$exists = true;
		break;
	}

	$column_name   = $column['column_name'];
	$column_number = intval(substr($column_name, strlen('LOG_FIELD')));
	$col_i         = max($col_i, $column_number);
	$sort_i        = max($sort_i, $column_number);
}

if(!$exists) {
	foreach($columns as $column_id => $column) {
		$column = new CustomFieldLogColumn($column_id);

		$column
			->setSystem(1)
			->persist();
	}

	$column_name = "LOG_FIELD" . ++$col_i;
	$new_column  = new CustomFieldLogColumn();

	$new_column
		->setFieldId($field['id'])
		->setTitle($title)
		->setColumnName($column_name)
		->setType('select')
		->setSortOrder(++$sort_i)
		->setSystem(1)
		->persist();

	$column_id   = intval($new_column->getId());
	$new_options = [];

	foreach($options as $option) {
		$new_option = new CustomFieldSelectOption();

		$new_option
			->setSourceClass('CustomFieldLogColumn')
			->setSourceId($column_id)
			->setRecord($option);

		$new_options[] = $new_option;
	}

	CustomFieldSelectOption::insert($new_options);

	// Copy permissions for the new column
	foreach(['edit', 'view'] as $access_type) {
		$new_objects = [];
		$old_key     = "SISUser:{$field['id']}:can_{$access_type}";
		$new_key     = "SISUser:{$field['id']}#{$column_id}:can_{$access_type}";

		$sql = "
			SELECT DISTINCT
				p1.profile_id
			FROM
				permission p1
			WHERE
				p1.\"key\" = :old_key AND
				NOT EXISTS(
					SELECT
						1
					FROM
						permission p2
					WHERE
						p2.\"key\" = :new_key AND
						p2.profile_id = p1.profile_id
				)
		";

		$params = [
			'old_key' => $old_key,
			'new_key' => $new_key
		];

		$rows = Database::get($sql, $params);

		foreach($rows as $row) {
			$profile_id = intval($row['PROFILE_ID']);

			$object = new Permission();

			$object
				->setProfileId($profile_id)
				->setKey($new_key);

			$new_objects[] = $object;
		}

		if(!empty($new_objects)) {
			Permission::insert($new_objects);
		}
	}
}
