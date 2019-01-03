<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_journal_detail", "pr_run_id")) {
	Database::createColumn("gl_journal_detail", "pr_run_id", "BIGINT");
}

$new_details = [];

// Update old journals to new sources
$sql = 
	"UPDATE
		gl_journals
	SET
		amount =
			CASE
				WHEN
					source = 'Generic Entry Encumbered'
				THEN
					encumbered * -1
				ELSE
					amount
			END,
		source = 
			CASE
				WHEN
					source = 'Payroll Re-allocation'
				THEN
					'Payroll Reallocated'
				WHEN
					source = 'PR Run Committed'
				THEN
					'PR Run Encumbered'
				WHEN
					source = 'Generic Entry Encumbered'
				THEN
					'PR Run Unencumbered'
				WHEN
					source LIKE 'PR Net Pay%'
				THEN
					'PR Net Pay'
			END
	FROM
		gl_pr_run_controls rc
	WHERE
		rc.id = gl_journals.source_parent_id AND
		(
			gl_journals.source IN ('Payroll Re-allocation', 'PR Run Committed', 'Generic Entry Encumbered') OR
			gl_journals.source LIKE 'PR Net Pay%'
		)";

Database::query($sql);

// Create journal details for easily-identifiable payroll journals that don't have details
$sql = 
	"SELECT
		j.id,
		j.source_parent_id
	FROM
		gl_journals j
	JOIN
		gl_pr_run_controls rc
	ON
		rc.id = j.source_parent_id
	LEFT JOIN
		gl_journal_detail jd
	ON
		jd.journal_id = j.id
	WHERE
		COALESCE(jd.id, 0) = 0";
$res = Database::get($sql);

foreach ($res as $data) {
	$journal_id = $data["ID"];
	$run_id     = $data["SOURCE_PARENT_ID"];

	$new_details[] = [
		"JOURNAL_ID" => $journal_id,
		"PR_RUN_ID"  => $run_id
	];
}

// Update details for journals linked directly to payroll runs
$sql = 
	"UPDATE
		gl_journal_detail
	SET
		pr_run_id = rc.id
	FROM
		gl_journals j,
		gl_pr_run_controls rc
	WHERE
		j.id = gl_journal_detail.journal_id AND
		rc.id = j.source_parent_id AND
		COALESCE(gl_journal_detail.pr_run_id, 0) = 0";

Database::query($sql);

// Update details for journals linked to checks of payroll batches
$sql = 
	"UPDATE
		gl_journal_detail
	SET
		pr_run_id = COALESCE(b.pr_run_id, c.run_control_id)
	FROM
		gl_ba_checks c,
		gl_batches b
	WHERE
		c.id = gl_journal_detail.check_id AND
		b.batch_id = c.batch_id AND
		COALESCE(b.pr_run_id, c.run_control_id, 0) != 0 AND
		COALESCE(gl_journal_detail.pr_run_id, 0) = 0";

Database::query($sql);

// Update details for journals linked to invoices of payroll batches
$sql = 
	"UPDATE
		gl_journal_detail
	SET
		pr_run_id = b.pr_run_id
	FROM
		gl_ap_invoice i,
		gl_batches b
	WHERE
		i.invoice_id = gl_journal_detail.ap_invoice_id AND
		b.batch_id = i.batch_id AND
		COALESCE(b.pr_run_id, 0) != 0 AND
		COALESCE(gl_journal_detail.pr_run_id, 0) = 0";

Database::query($sql);

// Update "Payroll Void" to negate expended and increase encumbered
$sql =
	"UPDATE
		gl_journals
	SET
		credit_account_id = debit_account_id,
		debit_account_id = credit_account_id,
		amount = amount * -1,
		expended = 
			CASE
				WHEN
					source = 'Payroll Void'
				THEN
					amount
				ELSE
					0
			END,
		encumbered =
			CASE
				WHEN
					source = 'Payroll Void'
				THEN
					amount * -1
				ELSE
					amount * -1
			END
	WHERE
		source IN ('Payroll Void', 'Payroll Reallocated')";

Database::query($sql);

// Void and reallocate journals with no object should not be modifying expended/encumbered
$object_id = ElementCategory::getObjectCategoryId(true);

if ($object_id) {
	$object_name = (new ElementCategory($object_id))->getName();
	$sql         =
		"UPDATE
			gl_journals
		SET
			expended = 0,
			encumbered = 0,
			source =
				CASE
					WHEN
						source = 'Payroll Void'
					THEN
						'Payroll Void Balance Only'
					ELSE
						'Payroll Reallocated Balance Only'
				END
		WHERE
			id IN 
				(
					SELECT 
						j.id 
					FROM 
						gl_journals j 
					JOIN 
						gl_accounting_strip s 
					ON 
						s.id = j.accounting_strip_id AND 
						COALESCE(s.{$object_name}, 0) = 0 
					WHERE 
						j.source IN ('Payroll Void', 'Payroll Reallocated')
				)";

	Database::query($sql);
}

if ($new_details) {
	Database::insert(JournalDetail::$table, JournalDetail::$sequence, array_keys($new_details[0]), $new_details);
}

Database::commit();
return true;
?>