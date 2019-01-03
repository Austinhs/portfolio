<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$receipt_lines = POSReceiptLine::getAllAndLoad("deposit_id is not null");

foreach($receipt_lines as $line) {
	(new ARDepositLine)
		->setReceiptLineId($line->getId())
		->setDepositId($line->getDepositId())
		->persist();
}

$refunds = POSReceiptLine::getAllAndLoad("deposit_id is not null");

foreach($refunds as $refund) {
	(new ARDepositLine)
		->setRefundId($refund->getId())
		->setDepositId($refund->getDepositId())
		->persist();
}
