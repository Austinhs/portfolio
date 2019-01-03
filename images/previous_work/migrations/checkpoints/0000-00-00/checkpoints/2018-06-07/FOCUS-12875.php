<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pos_receipt", "posted_date")) {
	$type = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";

	Database::createColumn("gl_pos_receipt", "posted_date", $type);

	$sql =
		"UPDATE
			gl_pos_receipt
		SET
			posted_date =
				(
					SELECT
						MAX(l.log_time)
					FROM
						database_object_log l
					JOIN
						gl_pos_receipt r
					ON
						r.id = l.record_id AND
						l.record_class = 'POSReceipt' AND
						r.id = gl_pos_receipt.id
					WHERE
						l.after LIKE '%\"POSTED\":1%'
				)
		WHERE
			posted = 1";

	Database::query($sql);

	$sql =
		"UPDATE
			gl_pos_receipt
		SET
			posted_date = date
		WHERE
			posted_date IS NULL AND
			posted = 1";

	Database::query($sql);
}

if (!Database::tableExists("gl_pos_receipt_invoice_payment")) {
	$sql =
		"CREATE TABLE gl_pos_receipt_invoice_payment (
			id BIGINT PRIMARY KEY,
			receipt_line_id BIGINT,
			invoice_allocation_id BIGINT,
			amount NUMERIC(28,10)
		)";

	Database::query($sql);

	$sql =
		"UPDATE
			gl_pos_receipt_invoice_payment
		SET
			id = {{next:gl_maint_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);

	// Now create POSReceiptInvoicePayment entries for all current and past receipt lines linked to invoices.
	$sql    =
		"SELECT
			id,
			invoice_allocation_hash
		FROM
			gl_pos_receipt_line
		WHERE
			invoice_allocation_hash IS NOT NULL AND
			invoice_allocation_hash != '[]' AND
			COALESCE(deleted, 0) = 0";
	$lines  = Database::get($sql);
	$insert = [];

	foreach ($lines as $line) {
		$line_id     = $line["ID"];
		$hash        = json_decode($line["INVOICE_ALLOCATION_HASH"]);
		$ids         = implode(", ", $hash);
		$sql         =
			"SELECT
				id,
				ROUND
					(
						ROUND(COALESCE(price, 0) * COALESCE(quantity, 0), 2) +
						ROUND(COALESCE(local_tax, 0), 2) +
						ROUND(COALESCE(state_tax, 0), 2),
						2
					) AS amount
			FROM
				gl_pos_invoice_allocation
			WHERE
				id IN ({$ids})";
		$allocations = Database::get($sql);

		foreach ($allocations as $allocation) {
			$insert[] = [
				"invoice_allocation_id" => $allocation["ID"],
				"amount"                => $allocation["AMOUNT"],
				"receipt_line_id"       => $line_id
			];
		}
	}

	if ($insert) {
		$columns = array_keys($insert[0]);

		Database::insert("gl_pos_receipt_invoice_payment", "gl_maint_seq", $columns, $insert);
	}

	$sql =
		"UPDATE
			gl_pos_receipt_line
		SET
			amount =
				(
					SELECT
						COALESCE(SUM(COALESCE(amount, 0)), 0)
					FROM
						gl_pos_receipt_invoice_payment p
					WHERE
						p.receipt_line_id = gl_pos_receipt_line.id
				)
		FROM
			gl_pos_receipt r
		WHERE
			r.id = gl_pos_receipt_line.receipt_id AND
			r.receipt_type = 'D' AND
			COALESCE(r.posted, r.deleted, gl_pos_receipt_line.deleted, 0) = 0 AND
			r.source = 'receipts' AND
			COALESCE(gl_pos_receipt_line.invoice_allocation_hash, '[]') != '[]'";

	Database::query($sql);
}

Database::commit();
return true;
?>