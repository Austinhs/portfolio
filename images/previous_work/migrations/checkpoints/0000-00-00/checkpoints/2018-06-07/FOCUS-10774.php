<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Migrations::depend("FOCUS-12992");
Database::begin();

$sql =
	"SELECT DISTINCT
		jd.ap_invoice_id,
		jd.check_id,
		b.pr_run_id
	FROM
		gl_batches b 
	LEFT JOIN
		gl_ap_invoice i
	ON
		i.batch_id = b.batch_id
	LEFT JOIN
		gl_ba_check_lines cl
	ON
		cl.parent_id = i.invoice_id
	LEFT JOIN
		gl_ba_checks c
	ON
		c.id = cl.check_id
	JOIN
		gl_journals j
	ON
		j.source_parent_id = c.id OR
		j.source_parent_id = i.invoice_id
	JOIN
		gl_journal_detail jd
	ON
		jd.journal_id = j.id
	WHERE 
		COALESCE(b.pr_run_id, 0) != 0";
$res = Database::get($sql);

foreach ($res as $data) {
	$invoice_id = $data["AP_INVOICE_ID"];
	$check_id   = $data["CHECK_ID"];
	$run_id     = $data["PR_RUN_ID"];
	$params     = [
		"invoice_id" => $invoice_id,
		"check_id"   => $check_id,
		"run_id"     => $run_id
	];
	$sql        =
		"UPDATE
			gl_journal_detail
		SET
			pr_run_id = :run_id
		WHERE
			check_id = :check_id OR
			ap_invoice_id = :invoice_id";

	Database::query($sql, $params);
}

$sql =
	"UPDATE
		gl_journal_detail
	SET
		pr_run_id = b.pr_run_id
	FROM
		gl_batches b,
		gl_ba_checks c,
		gl_journals j
	WHERE
		COALESCE(b.pr_run_id, 0) != 0 AND
		c.batch_id = b.batch_id AND
		j.source_parent_id = c.id AND
		j.id = gl_journal_detail.journal_id AND
		COALESCE(gl_journal_detail.pr_run_id, 0) = 0";

Database::query($sql);
Database::commit();
return true;
?>