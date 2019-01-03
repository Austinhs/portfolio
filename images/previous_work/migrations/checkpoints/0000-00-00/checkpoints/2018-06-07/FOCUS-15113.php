<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_pos_invoice_allocation_strip")) {
	$sql =
		"CREATE TABLE gl_pos_invoice_allocation_strip (
			id BIGINT PRIMARY KEY,
			deleted INT,
			invoice_allocation_id BIGINT,
			accounting_strip_id BIGINT,
			accounting_strip_hash VARCHAR(255),
			\"percent\" NUMERIC(28, 10)
		)";

	Database::query($sql);
}

if (!Database::columnExists("gl_pos_payment", "invoice_allocation_accounting_strip_id")) {
	Database::createColumn("gl_pos_payment", "invoice_allocation_accounting_strip_id", "BIGINT");

	$sql =
		"UPDATE
			gl_pos_payment
		SET
			invoice_allocation_accounting_strip_id = ia.accounting_strip_id
		FROM
			gl_pos_invoice_allocation ia
		WHERE
			ia.id = gl_pos_payment.invoice_allocation_id";

	Database::query($sql);
}

if (!Database::tableExists("gl_ar_warehouse_item_allocation")) {
	$sql =
		"CREATE TABLE gl_ar_warehouse_item_allocation (
			id BIGINT PRIMARY KEY,
			deleted INT,
			item_id BIGINT,
			percentage NUMERIC(28, 10),
			accounting_strip_id BIGINT,
			accounting_strip_hash VARCHAR(255)
		)";

	Database::query($sql);

	$sql =
		"INSERT INTO
			gl_ar_warehouse_item_allocation
				(id, item_id, percentage, accounting_strip_id, accounting_strip_hash)
		SELECT
			{{next:gl_maint_seq}} AS id,
			i.id AS item_id,
			100 AS percentage,
			s.id AS accounting_strip_id,
			s.hash AS accounting_strip_hash
		FROM
			gl_wh_items i
		JOIN
			gl_wh_pools p
		ON
			p.id = i.pool_id
		JOIN
			gl_accounting_strip s
		ON
			s.id = i.accounting_strip_id
		WHERE
			p.pos_active = 1 AND
			s.category_type IN (-3, -2) AND
			COALESCE(i.deleted, 0) = 0";
	$sql = Database::preprocess($sql);

	Database::query($sql);

	$sql =
		"INSERT INTO
			gl_pos_invoice_allocation_strip
				(
					id,
					invoice_allocation_id,
					accounting_strip_id,
					accounting_strip_hash,
					\"percent\"
				)
		SELECT
			{{next:gl_maint_seq}} AS id,
			id AS invoice_allocation_id,
			accounting_strip_id,
			accounting_strip_hash,
			100 AS \"percent\"
		FROM
			gl_pos_invoice_allocation
		WHERE
			COALESCE(accounting_strip_id, 0) != 0";
	$sql = Database::preprocess($sql);

	Database::query($sql);

	if (Database::$type === "mssql") {
		$indices = array_filter(
			Database::getIndexes("gl_pos_invoice_allocation"),
			function($record) {
				return (in_array("accounting_strip_id", $record));
			}
		);

		foreach ($indices as $index => $foo) {
			$sql =
				"DROP INDEX
					{$index}
				ON
					gl_pos_invoice_allocation";

			Database::query($sql);
		}
	}

	Database::changeColumnType("gl_pos_invoice_allocation", "accounting_strip_id", "TEXT");

	$sql =
		"UPDATE
			gl_pos_invoice_allocation
		SET
			accounting_strip_id = CONCAT('{\"', accounting_strip_id, '\":1}')
		WHERE
			COALESCE(accounting_strip_id, '0') != '0'";

	Database::query($sql);
}

Database::commit();
return true;
?>