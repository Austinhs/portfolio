<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::query("UPDATE permission SET \"key\" = 'ap::ap::display_credit_gl' WHERE \"key\" = 'ap::display_gl'");
Database::query("UPDATE permission SET \"key\" = 'ap::ap::display_internal_credit_gl' WHERE \"key\" = 'ap::display_internal_gl'");
