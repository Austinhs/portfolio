<?php

if(!$GLOBALS['FocusFinanceConfig']['enabled'] || !Database::tableExists('ps_fee_history')) {
	return false;
}

if(!Database::columnExists('ps_fee_history', 'mp_short_name')) {
	Database::createColumn('ps_fee_history', 'mp_short_name', 'varchar(255)');
}

?>
