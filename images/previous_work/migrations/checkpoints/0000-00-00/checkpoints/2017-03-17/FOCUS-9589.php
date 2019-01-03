<?php

Migrations::depend('FOCUS-11459');

//Field
$_SESSION['__migration_skip_views__'] = true;

$field = new CustomField;
$field
	->setSourceClass('SISUser')
	->setTitle('Staff Attendance')
	->setAlias("staff_attendance")
	->setSystem(1)
	->setType('log')
	->persist();
$fieldId = $field->getId();

//Columns
$column = new CustomFieldLogColumn;
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD1')
	->setType('date')
	->setTitle('Date')
	->setSortOrder(1)
	->persist();

$column = new CustomFieldLogColumn;
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD2')
	->setType('select')
	->setTitle('Present')
	->setSortOrder(2)
	->persist();

$colId = $column->getId();

$option = new CustomFieldSelectOption;
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('P')
	->setLabel('Present')
	->persist();
$option = new CustomFieldSelectOption;
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('A')
	->setLabel('Absent')
	->persist();

$column = new CustomFieldLogColumn;
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD3')
	->setType('select')
	->setTitle('Lunch')
	->setSortOrder(3)
	->persist();

$colId = $column->getId();

$option = new CustomFieldSelectOption;
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('BL')
	->setLabel('Brought Lunch')
	->persist();

$option = new CustomFieldSelectOption;
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('HL')
	->setLabel('Hot Lunch')
	->persist();

$option = new CustomFieldSelectOption;
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('S')
	->setLabel('Salad')
	->persist();

$option = new CustomFieldSelectOption;
$option
	->setSourceClass('CustomFieldLogColumn')
	->setSourceId($colId)
	->setCode('PBJ')
	->setLabel('PB & J')
	->persist();

$column = new CustomFieldLogColumn;
$column
	->setFieldId($fieldId)
	->setColumnName('LOG_FIELD4')
	->setType('text')
	->setTitle('Substitute')
	->setSortOrder(4)
	->persist();

unset($_SESSION['__migration_skip_views__']);

SISUser::refreshViews();
