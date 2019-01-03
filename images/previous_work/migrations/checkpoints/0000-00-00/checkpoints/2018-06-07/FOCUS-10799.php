<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql = 
	"UPDATE 
		focus_files 
	SET 
		file_expiration = 9999999999 
	WHERE 
		source = 'wh-items-product-image'";

Database::begin();
Database::query($sql);
Database::commit();
return true;
?>