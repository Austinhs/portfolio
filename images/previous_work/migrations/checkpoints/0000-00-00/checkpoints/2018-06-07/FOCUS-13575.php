<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!GLLedger::modifiedAccrual()) {
	return true;
}

Database::begin();

$sql =
	"DELETE FROM
		gl_journals
	WHERE
		id IN
			(
				SELECT
					j.id
				FROM
					gl_journal_detail jd
				JOIN
					gl_ap_invoice i
				ON
					i.invoice_id = jd.ap_invoice_id
				JOIN
					gl_batches b
				ON
					b.batch_id = i.batch_id AND
					b.batch_type = 'pcard_repayment'
				JOIN
					gl_journals j
				ON
					j.id = jd.journal_id AND
					j.source = '" . UpdateLedger::AP_CHK_UNCOM . "'
			)";

Database::query($sql);
Database::commit();
return true;
?>