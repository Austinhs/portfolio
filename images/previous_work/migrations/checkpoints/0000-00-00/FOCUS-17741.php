<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_wo_request_allocation")) {
	$sql =
		"CREATE TABLE gl_wo_request_allocation (
			id BIGINT PRIMARY KEY,
			deleted INT,
			request_id BIGINT,
			accounting_strip_id BIGINT,
			accounting_strip_hash VARCHAR(255),
			amount NUMERIC(28,10)
		)";

	Database::query($sql);
}

if (!Database::columnExists("gl_wo_request_type", "allow_allocations")) {
	Database::createColumn("gl_wo_request_type", "allow_allocations", "INT");
}

if (!Database::columnExists("gl_journal_detail", "work_order_id")) {
	Database::createColumn("gl_journal_detail", "work_order_id", "BIGINT");
}

if (!Database::columnExists("gl_wo_request", "negative_budget")) {
	Database::createColumn("gl_wo_request", "negative_budget", "INT");
}

if (!Database::columnExists("gl_wo_request", "fiscal_year")) {
	Database::createColumn("gl_wo_request", "fiscal_year", "INT");
}

Database::commit();
return true;
?>