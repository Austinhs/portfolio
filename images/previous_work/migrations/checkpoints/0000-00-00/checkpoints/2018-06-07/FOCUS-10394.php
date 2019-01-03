<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_accounting_strip")) {
	return false;
}

$categories = ElementCategory::getAllAndLoad([
	"id != -1",
	"COALESCE(deleted, 0) = 0"
]);

Database::begin();

foreach ($categories as $category) {
	$name = $category->getName();

	if (!$name) {
		continue;
	}

	$sql =
		"UPDATE 
			gl_accounting_strip
		SET 
			{$name} = NULL 
		WHERE 
			COALESCE({$name}, 0) = 0";

	Database::query($sql);
}

Database::commit();

return true;
?>