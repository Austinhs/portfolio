<?php
if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled'])
	return false;

$setting = Setting::getOne("\"key\" = 'ach_employee_id'");

if (empty($setting))
	$setting = (new Setting())->setKey("ach_employee_id");

$setting->setValue('ein')->persist();
