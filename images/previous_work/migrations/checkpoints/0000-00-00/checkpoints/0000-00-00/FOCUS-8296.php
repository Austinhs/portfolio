<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::begin();

$sql = "
	UPDATE
		gl_journals
	SET
		source_record_id = ra.id
	FROM
		gl_ap_request_allocation ra
	JOIN
		gl_ap_request r
	ON
		r.id = ra.request_id
	WHERE
		r.id = source_parent_id AND
		ra.accounting_strip_id = gl_journals.accounting_strip_id AND
		journal_fiscal_year = 2016 AND
		gl_journals.source IN (
			'AP Order Rollover',
			'AP Order Rollover Budget'
		)
";

Database::query($sql);

Database::commit();
