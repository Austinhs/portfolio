<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql = "
	UPDATE gl_manual_journal_draft
	SET link_type = 'ap_invoice'
	WHERE link_type = 'invoice'
";

Database::begin();

// All original records referred to as `invoice` are AP Invoices which need to be updated because of AR Invoices
Database::query($sql);

Database::commit();

return true;

