<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

if(!Database::columnExists('ps_fee_groups', 'course_period_id')) {
	Database::createColumn('ps_fee_groups', 'course_period_id', 'bigint');
}
