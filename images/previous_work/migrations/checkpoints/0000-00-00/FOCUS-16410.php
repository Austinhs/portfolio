<?php

// Escambia already named their column 'custom_20009708'
if(!empty($GLOBALS['ClientId']) && intval($GLOBALS['ClientId']) === 8468) {
	$field = CustomField::getOneAndLoad("LOWER(column_name) = 'custom_20009708'");

	$field
		->setAlias('protected_student')
		->setSystem(1)
		->persist();
}
else {
	$tmp = Database::get("
		SELECT
			1
		FROM
			custom_fields
		WHERE
			alias = 'protected_student' AND
			system = 1 AND
			deleted IS NULL
	");

	if(empty($tmp)) {
		(new CustomField())
			->setSourceClass('SISStudent')
			->setType('checkbox')
			->setTitle('Protected Student')
			->setColumnName('protected_student')
			->setAlias('protected_student')
			->setSystem(1)
			->persist();
	}
}
