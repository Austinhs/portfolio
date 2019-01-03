<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::query("UPDATE gl_journals SET source = 'Void AP Warrants Unexpended' WHERE source = 'Void AP Warrants Expended'");
