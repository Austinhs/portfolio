<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_ap_invoice", "template")) {
	Database::createColumn("gl_ap_invoice", "template", "INT");
}

if (!Database::columnExists("gl_ap_invoice", "template_id")) {
	Database::createColumn("gl_ap_invoice", "template_id", "BIGINT");
}

if (!Database::tableExists("gl_ap_invoice_template")) {
	$sql =
		"CREATE TABLE gl_ap_invoice_template (
			id BIGINT PRIMARY KEY,
			deleted INT,
			name VARCHAR(255),
			created_by BIGINT,
			type VARCHAR(255),
			fiscal_year BIGINT,
			internal INT
		)";

	Database::query($sql);

	$sql =
		"UPDATE
			gl_ap_invoice_template
		SET
			id = {{next:gl_maint_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);
}

Database::commit();
return true;
?>