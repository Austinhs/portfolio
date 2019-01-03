<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql =
	"UPDATE
		gl_journal_detail
	SET
		dealer_id = i.customer_id
	FROM
		gl_pos_invoice i
	WHERE
		i.id = gl_journal_detail.pos_invoice_id AND
		COALESCE(gl_journal_detail.dealer_id, 0) = 0";

Database::query($sql);
return true;
?>