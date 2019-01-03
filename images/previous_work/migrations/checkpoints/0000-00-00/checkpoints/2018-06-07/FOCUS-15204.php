<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

Database::query("UPDATE gl_batches SET source = 'PR Net Pay' WHERE source LIKE 'PR Net Pay %'");

$batches = Batch::getAll("name LIKE '%PR Net Pay for run: Manual'");
$runs    = RunControls::getAll();

foreach ($batches as $batch)
{
	if (!isset($runs[$batch->getPrRunId()]))
		continue;

	$batch->setName(preg_replace('/: Manual/', ": " . $runs[$batch->getPrRunId()]->getTitle(), $batch->getName()))->persist();
}

Database::commit();
