<?php

if(!$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$batches = Batch::getAll("name LIKE 'Quick Check%' AND source = 'Invoice Page'");
if(empty($batches)) {
	return false;
}

Database::begin();

$in       = implode(', ', array_keys($batches));
//$invoices = Invoice::getAllAndLoad("batch_id IN ({$in})");
$invoices = [];

foreach($invoices as $invoice_id => $invoice) {
	$exists = Journal::getOne("source_parent_id = {$invoice_id} AND source = 'AP Invoice Expended'");

	if(!empty($exists)) {
		continue;
	}

	$batch = new Batch($invoice->getBatchId());

	if($batch->getPosted() !== 'Y') {
		continue;
	}

	$invoice->post($batch->getFiscalYear());
}

Database::commit();
