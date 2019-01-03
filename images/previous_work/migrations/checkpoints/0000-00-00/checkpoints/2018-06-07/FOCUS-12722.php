<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_fa_asset_allocation", "request_line_item_id")) {
	Database::createColumn("gl_fa_asset_allocation", "request_line_item_id", "BIGINT");
}

if (!Database::columnExists("gl_fa_asset_allocation", "invoice_id")) {
	Database::createColumn("gl_fa_asset_allocation", "invoice_id", "BIGINT");

	$sql =
		"UPDATE
			gl_fa_asset_allocation
		SET
			invoice_id = li.invoice_id,
			request_line_item_id = li.request_line_item_id
		FROM
			gl_fa_asset a,
			gl_ap_invoice_line_item li
		WHERE
			a.id = gl_fa_asset_allocation.asset_id AND
			li.request_line_item_id = a.line_item_id";

	Database::query($sql);
}

Database::commit();
return true;
?>