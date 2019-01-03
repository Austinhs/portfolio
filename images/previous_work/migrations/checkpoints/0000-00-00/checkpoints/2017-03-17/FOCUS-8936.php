<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

if(!Database::tableExists('gl_pos_cashout_facility')) {
	// This is only temporary: Metadata will add indexes as needed
	// and fix up the table properly, this is to prevent errors.
	Database::query(
		"
			CREATE TABLE gl_pos_cashout_facility (
				id bigint,
				deleted bigint,
				facility_id bigint,
				accounting_strip_id bigint,
				accounting_strip_hash varchar(255),
				debit_account_id bigint,
				credit_account_id bigint,
				internal bigint
			)
		"
	);
}
