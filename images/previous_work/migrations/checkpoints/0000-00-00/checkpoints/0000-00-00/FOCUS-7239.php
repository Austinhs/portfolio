<?php

Migrations::depend('FOCUS-6398');

if(purchasedCTE()) {
	if(!Database::columnExists('ps_fees_one_time', 'ten_ninety_eight')) {
		Database::createColumn('ps_fees_one_time', 'ten_ninety_eight', 'bigint');
	}

	if(!Database::columnExists('ps_fees_one_time', 'item_number')) {
		Database::createColumn('ps_fees_one_time', 'item_number', 'varchar(255)');
	}

	$one_time_fees = "
		SELECT
			p.SCHOOL_ID,
			c.id as HISTORICAL_ID,
			c.resident,
			COALESCE(p.ten_ninety_eight, c.ten_ninety_eight) as ten_ninety_eight,
			p.item_number,
			p.syear,
			c.accounting_strip_id,
			p.title,
			c.amount,
			p.syear,
			c.accounting_strip_hash
		FROM
			ps_fees_one_time p
			JOIN ps_fees_one_time c on c.parent_id = p.id and p.parent_id is null
	";

	$old_one_time_fees = Database::get($one_time_fees);

	foreach($old_one_time_fees as $old_fee) {
		extract($old_fee);

		if(in_array($RESIDENT, array(1, '1', true, 't'))) {
			$RESIDENT_ACCOUNTING_STRIP_ID       = $ACCOUNTING_STRIP_ID;
			$RESIDENT_ACCOUNTING_STRIP_HASH     = $ACCOUNTING_STRIP_HASH;
			$RESIDENT_AMOUNT                    = $AMOUNT;
			$NON_RESIDENT_ACCOUNTING_STRIP_ID   = NULL;
			$NON_RESIDENT_ACCOUNTING_STRIP_HASH = NULL;
			$NON_RESIDENT_AMOUNT                = 0;
		} else {
			$NON_RESIDENT_ACCOUNTING_STRIP_ID   = $ACCOUNTING_STRIP_ID;
			$NON_RESIDENT_ACCOUNTING_STRIP_HASH = $ACCOUNTING_STRIP_HASH;
			$NON_RESIDENT_AMOUNT                = $AMOUNT;
			$RESIDENT_ACCOUNTING_STRIP_ID       = NULL;
			$RESIDENT_ACCOUNTING_STRIP_HASH     = NULL;
			$RESIDENT_AMOUNT                    = 0;
		}

		$fee = (new PsFees)
			->setSchoolId($SCHOOL_ID)
			->setResidentAmount($RESIDENT_AMOUNT)
			->setNonResidentAmount($NON_RESIDENT_AMOUNT)
			->setNonResidentAccountingStripId($NON_RESIDENT_ACCOUNTING_STRIP_ID)
			->setResidentAccountingStripId($RESIDENT_ACCOUNTING_STRIP_ID)
			->setNonResidentAccountingStripHash($NON_RESIDENT_ACCOUNTING_STRIP_HASH)
			->setSyear($SYEAR)
			->setResidentAccountingStripHash($RESIDENT_ACCOUNTING_STRIP_HASH)
			->setTitle($TITLE)
			->setSyear($SYEAR)
			->setItemNumber($ITEM_NUMBER)
			->setAnnualFee(1)
			->persist();

		$old_records_sql = "
			SELECT
				*
			FROM
				ps_fees_one_time_paid
			WHERE
				fee_id = {$HISTORICAL_ID}
		";

		$old_records = Database::get($old_records_sql);

		foreach($old_records as $record) {
			(new PsFeeHistory)
				->setFeeId($fee->GetId())
				->setStudentId($record['STUDENT_ID'])
				->setSyear($record['SYEAR'])
				->setUniqueFeeId($fee->getUniqueFeeId())
				->persist();
		}
	}
} else {
	return false;
}
