<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

if(!Database::tableExists(CustomerCreditAccount::$table)) {
	$GLOBALS['InstallingFinance'] = true;
	MetaData::importMetaData();
	$GLOBALS['InstallingFinance'] = false;

	$transaction_types = [
		[
			"title"       => "usage",
			"description" => "Customer Credit Usage",
			"operation"   => -1,
		],
		[
			"title"       => "application",
			"description" => "Customer Credit Application",
			"operation"   => 1,
		],
		[
			"title"       => "refund",
			"description" => "Customer Credit Refund",
			"operation"   => 1,
		],
		[
			"title"       => "refunded",
			"description" => "Customer Credit Refunded",
			"operation"   => -1,
		],
		[
			"title"       => "transfer",
			"description" => "Customer Credit Transfer",
			"operation"   => 1,
		]
	];

	foreach($transaction_types as $type) {
		(new CustomerCreditAccountTransactionType)
			->setTitle($type['title'])
			->setDescription($type['description'])
			->setOperation($type['operation'])
			->persist();
	}

	if(!Database::columnExists(CustomerCreditAccountTransaction::$table, 'transaction_number')) {
		Database::createColumn(CustomerCreditAccountTransaction::$table, 'transaction_number', 'bigint');
	}
}

$internal            = intval(Settings::get('internal_customer_credit'));
$accounting_strip_id = CustomerCreditFacility::generateInitialInstance($internal);
$prefix              = $internal ? 'internal_' : '';
$accounting_strip_id = intval(Settings::get("{$prefix}customer_credit_accounting_strip_id"));

$sql = "
	SELECT DISTINCT
		r.customer_id
	FROM
		" .POSReceipt::$table. " r
	JOIN
		" .POSReceiptAllocation::$table. " ra
	ON
		r.id = ra.receipt_id
	WHERE
		ra.accounting_strip_id = {$accounting_strip_id}
";

$receipts = Database::get($sql);

$type_id = CustomerCreditAccountTransactionType::getType('transfer');

foreach($receipts as $receipt) {
	$customer_id = $receipt['CUSTOMER_ID'];

	if(empty($customer_id)) {
		continue;
	}

	$account  = CustomerCreditAccount::getAccount($customer_id);
	$customer = new Customer($customer_id);
	$balance  = $customer->getReceiptBalance($accounting_strip_id);

	if($balance) {
		$customer_credit_transaction = new CustomerCreditAccountTransaction;
		$customer_credit_transaction
			->setAmount($balance)
			->setTransactionNumber()
			->setTypeId($credit_type_id)
			->setCustomerCreditAccountId($account->getId())
			->persist();
	}
}
