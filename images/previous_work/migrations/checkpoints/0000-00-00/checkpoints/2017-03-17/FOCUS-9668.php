<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::begin();

if(strpos($_SERVER['REQUEST_URI'], '/focus') !== false) {

	// Make sure hash is not null
	$sql = "
		ALTER TABLE
			gl_journals
		ADD CONSTRAINT
			gl_journals_hash_not_null
		CHECK (
			(accounting_strip_hash IS NOT NULL AND source != 'AP Request Change') OR
			(accounting_strip_hash IS NULL AND source = 'AP Request Change')
		)
	";

	Database::query($sql);

	// Make sure the state of purchase_order is good
	$sql = "
		ALTER TABLE
			gl_ap_request
		ADD CONSTRAINT
			gl_ap_request_purchase_order_con
		CHECK (
			(purchase_order IS NOT NULL AND request_status = 'Y' AND type NOT IN ('P', 'V')) OR
			(purchase_order IS NULL AND request_status = 'Y' AND type IN ('P', 'V')) OR
			(purchase_order IS NULL AND request_status != 'Y')
		)
	";

	Database::query($sql);

	// Make sure req number is not null
	$sql = "
		ALTER TABLE
			gl_ap_request
		ADD CONSTRAINT
			gl_ap_request_requisition_number_not_null
		CHECK (
			(requisition_number IS NOT NULL AND request_status NOT IN ('T')) OR
			(requisition_number IS NULL AND request_status IN ('T'))
		)
	";

	Database::query($sql);
}

Database::commit();
