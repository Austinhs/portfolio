<?php

// Skip this migration since it has probably never actually completed
return false;

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::begin();

$receipt_allocations = POSReceiptAllocation::getAllAndLoad("receipt_id in (select r.id from gl_pos_receipt r where r.source = 'pos')");

foreach($receipt_allocations as $old_receipt_allocation_id => $old_receipt_allocation) {
	$payments = $old_receipt_allocation->getPayments();

	if(empty($payments)) {
		continue;
	}

	// We're going to maintain the original allocation attached to one of the payments
	$original_payment   = array_shift($payments);
	$invoice_allocation = $original_payment->getInvoiceAllocation();
	$old_ra_record      = $old_receipt_allocation->getRecord();

	// Update query to bypass error validation
	$query = "
		UPDATE
			gl_pos_receipt_allocation
		SET
			amount = :amount,
			accounting_strip_id = :accounting_strip_id,
			debit_account_id = :debit_account_id,
			credit_account_id = :credit_account_id
		WHERE
			id = :id
	";

	$params = [
		'amount'              => $original_payment->getAmount(),
		'accounting_strip_id' => $invoice_allocation->getAccountingStripId(),
		'debit_account_id'    => $invoice_allocation->getDebitAccountId(),
		'credit_account_id'   => $invoice_allocation->getCreditAccountId(),
		'id'                  => $old_receipt_allocation_id
	];

	Database::query($query, $params);

	foreach($payments as $payment_id => $payment) {
		$new_invoice_allocation = $payment->getInvoiceAllocation();

		$receipt_allocation = $old_receipt_allocation->duplicate();
		$receipt_allocation
			->setAmount($payment->getAmount())
			->setAccountingStripId($new_invoice_allocation->getAccountingStripId())
			->setAccountingStripHash($new_invoice_allocation->getAccountingStripHash())
			->setDebitAccountId($new_invoice_allocation->getDebitAccountId())
			->setCreditAccountId($new_invoice_allocation->getCreditAccountId())
			->set_Ignored(true)
			->persist();

		$query = "
			UPDATE
				gl_pos_payment
			SET
				receipt_allocation_id = :ra_id
			WHERE
				id = :payment_id
		";

		$params = [
			'ra_id'      => $receipt_allocation->getId(),
			'payment_id' => $payment_id
		];

		Database::query($query, $params);
	}
}

Database::commit();
