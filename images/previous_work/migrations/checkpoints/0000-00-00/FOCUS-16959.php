<?php
$definition = FocusUser::getFieldByAlias('ssn');
if(empty($definition)) 
{
	$field = new CustomField();
	$field
	  ->setSourceClass('FocusUser')
	  ->setColumnName('custom_556')
	  ->setAlias('ssn')
	  ->setType('text')
	  ->setTitle('Social Security Number');
}
else 
{
	$field = new CustomField($definition['id']);
}

$field
 ->setSystem(1)
 ->persist();

$columnName = $field -> getColumnName();
if(!Database::columnExists('users', $columnName))
{
	Database::createColumn('users', $columnName,'varchar', '9');
}