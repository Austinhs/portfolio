<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

$params = [
	"source" => UpdateLedger::BA_WH_CHK_VOID
];
$sql    =
	"UPDATE
		gl_journals
	SET
		accounting_strip_id = s.id,
		accounting_strip_hash = s.hash
	FROM
		gl_ba_check_allocations ca,
		gl_accounting_strip s
	WHERE
		ca.id = gl_journals.source_record_id AND
		s.id = ca.accounting_strip_id AND
		gl_journals.source = :source";

Database::query($sql, $params);
return true;
?>