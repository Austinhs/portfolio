<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql =
	"WITH
		allocations AS (
			SELECT
				SUM(ROUND(ia.price * ia.quantity, 2) + ROUND(COALESCE(ia.state_tax, 0), 2) + ROUND(COALESCE(ia.local_tax, 0), 2)) AS amount,
				i.id AS invoice_id
			FROM
				gl_pos_invoice_allocation ia
			JOIN
				gl_pos_invoice i
			ON
				i.id = ia.invoice_id
			WHERE
				COALESCE(ia.deleted, 0) = 0 AND
				COALESCE(i.deleted, 0) = 0 AND
				COALESCE(i.paid, 0) = 0 AND
				ia.cancelled_date IS NULL AND
				(
					ia.price != 0 OR
					ia.description != 'Educational Credit'
				)
			GROUP BY
				i.id
		),
		payments AS (
			SELECT DISTINCT
				p.amount,
				p.id AS payment_id,
				i.id AS invoice_id
			FROM
				gl_pos_payment p
			JOIN
				gl_pos_invoice_allocation ia
			ON
				ia.id = p.invoice_allocation_id
			JOIN
				gl_pos_invoice i
			ON
				i.id = ia.invoice_id
			WHERE
				COALESCE(ia.deleted, 0) = 0 AND
				COALESCE(i.deleted, 0) = 0 AND
				COALESCE(p.deleted, 0) = 0 AND
				COALESCE(i.paid, 0) = 0 AND
				ia.cancelled_date IS NULL AND
				p.voided_date IS NULL AND
				(
					ia.price != 0 OR
					ia.description != 'Educational Credit'
				)
			GROUP BY
				p.amount,
				p.id,
				i.id
		),
		refunds AS (
			SELECT DISTINCT
				SUM(r.amount) AS amount,
				p.invoice_id
			FROM
				gl_pos_refund r
			JOIN
				payments p
			ON
				p.payment_id = r.payment_id
			WHERE
				COALESCE(r.deleted, 0) = 0 AND
				r.reinvoiced = 1
			GROUP BY
				p.invoice_id
		)
	SELECT
		i.id
	FROM
		gl_pos_invoice i
	JOIN
		allocations a
	ON
		a.invoice_id = i.id
	JOIN
		payments p
	ON
		p.invoice_id = i.id
	LEFT JOIN
		refunds r
	ON
		r.invoice_id = i.id
	GROUP BY
		i.id,
		a.amount,
		r.amount
	HAVING
		(a.amount - SUM(p.amount) + COALESCE(r.amount, 0)) <= 0";
$res = Database::get($sql);
$ids = [];

foreach ($res as $record) {
	$ids[] = $record["ID"];
}

if ($ids) {
	$ids = implode(", ", $ids);
	$sql =
		"UPDATE
			gl_pos_invoice
		SET
			paid = 1
		WHERE
			id IN ({$ids})";

	Database::query($sql);
}

Database::commit();
return true;
?>