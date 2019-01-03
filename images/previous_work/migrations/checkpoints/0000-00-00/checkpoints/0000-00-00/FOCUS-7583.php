<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$sql = "
	SELECT
		j.source_record_id,
		j.id
	FROM
		gl_journals j
	JOIN
		gl_pos_refund rf
	ON
		rf.id = j.source_record_id
	WHERE
		j.source = 'AR Invoice Reaccrued'
";

$journals = Database::get($sql);

foreach($journals as $journal) {
	$journal_object = new Journal($journal['ID']);
	$journal_object
		->setSource('AR Refund Invoice Reaccrued')
		->persist();
}
