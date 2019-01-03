<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$education = Education::getAllAndLoad();

foreach ($education as $e) {
	$e->markHighestDegree();
}

Database::commit();

return true;
