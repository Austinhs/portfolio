<?php

Migrations::depend('FOCUS-14843');

$field = FocusUser::getFieldByAlias('local_id');

if(!empty($field)) {
	$field_obj = new CustomField($field['id']);

	$field_obj
		->setSystem(1)
		->persist();
}
