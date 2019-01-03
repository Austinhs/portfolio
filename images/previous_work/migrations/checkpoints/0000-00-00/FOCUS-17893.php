<?php

if(empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if($GLOBALS["ClientId"] == 20769){
	Database::begin();
	Database::query(
		"UPDATE
			gl_ap_request
		SET
			auto_close = NULL
		WHERE
			TYPE = 'B'"
		);
	Database::commit();
}
?>