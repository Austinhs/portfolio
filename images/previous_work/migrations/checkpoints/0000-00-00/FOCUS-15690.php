<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql =
	"SELECT
		tmp.id
	FROM 
		(
			SELECT
				i.id,
				COALESCE
					(
						(
							CASE
								WHEN
									ia.cancelled_date IS NULL
								THEN 
									ROUND(COALESCE(ia.price * ia.quantity + COALESCE(ia.state_tax, 0) + COALESCE(ia.local_tax, 0), 0), 2)
								ELSE
									0 
							END
						), 
						0
					) AS owed, 
				COALESCE
					(
						(
							SELECT 
								SUM(COALESCE(p.amount, 0)) 
							FROM 
								gl_pos_payment p 
							WHERE 
								COALESCE(p.deleted, 0) = 0 AND
								p.voided_date IS NULL AND
								p.invoice_allocation_id = ia.id
						), 
						0
					) AS paid, 
				COALESCE
					(
						(
							SELECT 
								COALESCE(SUM(COALESCE(d.amount, 0)), 0) - COALESCE(SUM(COALESCE(p.amount, 0)), 0)
							FROM 
								gl_pos_deferral d 
							LEFT JOIN
								gl_pos_payment p
							ON
								p.source_class = 'POSDeferral' AND
								p.source_id = d.id
							WHERE 
								COALESCE(d.deleted, 0) = 0 AND
								COALESCE(p.deleted, 0) = 0 AND
								COALESCE(d.paid, 0) = 0 AND
								p.voided_date IS NULL AND
								COALESCE(d.overage, 0) = 0 AND
								d.invoice_allocation_id = ia.id
						), 
						0
					) AS deferred, 
				COALESCE
					(
						(
							SELECT 
								SUM(COALESCE(rf.amount, 0)) 
							FROM 
								gl_pos_refund rf 
							JOIN
								gl_pos_payment p
							ON
								p.id = rf.payment_id 
							WHERE 
								COALESCE(rf.deleted, 0) = 0 AND
								COALESCE(rf.reinvoiced, 0) = 1 AND
								p.invoice_allocation_id = ia.id AND
								p.voided_date IS NULL
						), 
						0
					) AS refunded 
			FROM 
				gl_pos_invoice i 
			JOIN
				gl_pos_invoice_allocation ia
			ON
				ia.invoice_id = i.id 
			WHERE 
				COALESCE(i.deleted, 0) = 0 AND
				COALESCE(ia.deleted, 0) = 0 AND
				i.paid = 1 AND
				ia.voided_date IS NULL AND
				ia.cancelled_date IS NULL
		) tmp
	GROUP BY
		tmp.id
	HAVING
		(
			COALESCE(SUM(tmp.owed), 0) -
			COALESCE(SUM(tmp.paid), 0) +
			COALESCE(SUM(tmp.refunded), 0) -
			COALESCE(SUM(tmp.deferred), 0)
		) != 0";
$res = Database::get($sql);
$ids = array_column($res, "ID");

if ($ids) {
	$ids      = implode(", ", $ids);
	$invoices = POSInvoice::getAllAndLoad([
		"id IN ({$ids})"
	]);

	foreach ($invoices as $invoice) {
		$invoice
			->updatePaid()
			->persist();
	}
}

Database::commit();
return true;
?>