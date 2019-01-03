<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$existing_types = CustomerCreditAccountTransactionType::getOne();

if (!$existing_types) {
	$transaction_types = [
		[
			"title"       => "usage",
			"description" => "Customer Credit Usage",
			"operation"   => -1
		],
		[
			"title"       => "application",
			"description" => "Customer Credit Application",
			"operation"   => 1
		],
		[
			"title"       => "refund",
			"description" => "Customer Credit Refund",
			"operation"   => 1
		],
		[
			"title"       => "refunded",
			"description" => "Customer Credit Refunded",
			"operation"   => -1
		],
		[
			"title"       => "transfer",
			"description" => "Customer Credit Transfer",
			"operation"   => 1
		]
	];

	foreach ($transaction_types as $type) {
		(new CustomerCreditAccountTransactionType)
			->setTitle($type["title"])
			->setDescription($type["description"])
			->setOperation($type["operation"])
			->persist();
	}
}

Database::commit();
return true;
?>