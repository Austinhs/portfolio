<?php
if($GLOBALS['FocusFinanceConfig']['enabled']) {
	if(Database::getColumnType('gl_comments', 'comment') !== 'text'){
		Database::changeColumnType('gl_comments', 'comment', 'text');
	}
} else {
	return false;
}
