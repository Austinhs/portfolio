<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$setting = Setting::getOne("\"key\" = 'filler_check_numbers'");

if(empty($setting)) {
	$setting = new Setting();
}

$setting
	->setKey('filler_check_numbers')
	->setValue('1')
	->persist();
