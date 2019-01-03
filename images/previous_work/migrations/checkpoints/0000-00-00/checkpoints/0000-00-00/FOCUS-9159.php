<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$sql = "
	UPDATE
		gl_ap_request_line_item
	SET
		price = amount
	WHERE
		price = 0 AND
		qty = 1 AND
		amount != 0
";
Database::query($sql);
