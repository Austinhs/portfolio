<?php
Database::begin();

Migrations::depend('FOCUS-7224a');

if (!class_exists('CustomFieldObject')) {
	// Focus v7
	$queries = [];
	$queries[] = "INSERT INTO custom_fields ( id, TYPE, category_id, title, select_options, select_option_codes, sort_order ) VALUES ( 2016110101, 'radio', 1, 'LCP Continuing Student', NULL, NULL, 0 )";
	$queries[] = "INSERT INTO custom_fields ( id, TYPE, category_id, title, select_options, select_option_codes, sort_order ) VALUES ( 2016110102, 'select', 1, 'CASAS Track', '
Listening
Reading', 'S
R', 0 )";
	$queries[] = "INSERT INTO student_fields_join_categories ( category_id, field_id, sort_order ) VALUES ( 1, 2016110101, 0 )";
	$queries[] = "INSERT INTO student_fields_join_categories ( category_id, field_id, sort_order ) VALUES ( 1, 2016110102, 0 )";

	$fieldKey = 2016110101;
	if (!Database::columnExists('students', "custom_{$fieldKey}")) {
		Database::createColumn('students', "custom_{$fieldKey}", 'VARCHAR');
	}
	$fieldKey = 2016110102;
	if (!Database::columnExists('students', "custom_{$fieldKey}")) {
		Database::createColumn('students', "custom_{$fieldKey}", 'VARCHAR');
	}

	foreach ($queries as $query) {
		Database::query($query);
	}
} else {
	// Depend on the main custom fields migration
	Migrations::depend('FOCUS-5468');
	Migrations::depend('FOCUS-11459');

	// Focus v8
	$category = CustomFieldCategory::getOne("legacy_id = 1");
	if (empty($category)) {
		throw new Exception("Missing student field category with ID 1. Please create this category and re-run migrations.");
	}
	$categoryId = $categoryId = $category->getId();

	// Continuing Student indicator
	$fieldKey = 2016110101;
	if (empty(CustomField::getOne('column_name = :name', false, ['name' => "custom_{$fieldKey}"]))) {
		$field = new CustomField;
		$field
			->setSourceClass('SISStudent')
			->setTitle('LCP Continuing Student')
			->setAlias("custom_{$fieldKey}")
			->setColumnName("custom_{$fieldKey}")
			->setSystem(1)
			->setType('checkbox')
			->persist();

		$fieldId = $field->getId();

		$join = new CustomFieldJoinCategory;
		$join
			->setFieldId($fieldId)
			->setCategoryId($categoryId)
			->setSortOrder(0)
			->persist();
	}

	// Student LCP Track
	$fieldKey = 2016110102;
	if (empty(CustomField::getOne('column_name = :name', false, ['name' => "custom_{$fieldKey}"]))) {
		$field = new CustomField;
		$field
			->setSourceClass('SISStudent')
			->setTitle('CASAS Track')
			->setAlias("custom_{$fieldKey}")
			->setColumnName("custom_{$fieldKey}")
			->setSystem(1)
			->setType('select')
			->persist();

		$fieldId = $field->getId();

		$options = [
			'R' => 'Reading',
			'S' => 'Listening'
		];
		foreach ($options as $key => $value) {
			$option = new CustomFieldSelectOption;
			$option
				->setSourceClass('CustomField')
				->setSourceId($fieldId)
				->setCode($key)
				->setLabel($value)
				->persist();
		}

		$join = new CustomFieldJoinCategory;
		$join
			->setFieldId($fieldId)
			->setCategoryId($categoryId)
			->setSortOrder(0)
			->persist();
	}

}

Database::commit();
