<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_wh_courier_requests", "request_number")) {
	Database::createColumn("gl_wh_courier_requests", "request_number", "BIGINT");

	$requests = WarehouseCourierRequest::getAllAndLoad();

	foreach ($requests as $request) {
		$request
			->setRequestNumber(Sequence::next("WarehouseCourierRequest"))
			->persist();
	}
}

Database::commit();
return true;
?>