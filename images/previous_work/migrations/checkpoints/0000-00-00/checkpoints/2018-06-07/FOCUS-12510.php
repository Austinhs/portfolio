<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if ($GLOBALS["ClientId"] !== 20692) {
	return true;
}

Database::begin();

$fund_name = ElementCategory::getFundCategory()->getName();
$date      = GLLedger::getFiscalStartingDate(2017);
$sql       =
	"SELECT
		SUM(COALESCE(j.accrued, 0) + COALESCE(j.rolled_accrued, 0)) AS amount,
		b.type,
		b.id AS budget_id,
		j.accounting_strip_id,
		s.{$fund_name} AS fund_id
	FROM
		gl_journals j
	JOIN
		gl_accounting_strip s
	ON
		s.id = j.accounting_strip_id
	JOIN
		gl_budget b
	ON
		b.accounting_strip_id = s.parent_id AND
		COALESCE(b.year, j.journal_fiscal_year) = 2016 AND
		COALESCE(b.deleted, 0) = 0
	WHERE
		b.type IN ('I', 'R')
	GROUP BY
		j.accounting_strip_id,
		b.id,
		b.type,
		s.{$fund_name}
	HAVING
		SUM(COALESCE(j.accrued, 0) + COALESCE(j.rolled_accrued, 0)) != 0";
$res       = Database::get($sql);
$journals  = [];

foreach ($res as $record) {
	$action    = ($record["TYPE"] === "I") ? UpdateLedger::AR_IA_ACC_ROLLOVER : UpdateLedger::AR_ACC_ROLLOVER;
	$amount    = $record["AMOUNT"];
	$strip_id  = $record["ACCOUNTING_STRIP_ID"];
	$budget_id = $record["BUDGET_ID"];
	$fund_id   = $record["FUND_ID"];

	if (!isset($journals[$action])) {
		$journals[$action] = [];
	}

	$journals[$action][] = [
		"FUND"                => $fund_id,
		"AMOUNT"              => $amount,
		"SOURCE_RECORD_ID"    => $budget_id,
		"SOURCE_PARENT_ID"    => $budget_id,
		"ACCOUNTING_STRIP_ID" => $strip_id,
		"DETAILS"             => [],
	];
}

if ($journals) {
	foreach ($journals as $action => $tmp_journals) {
		$internal = ($action === UpdateLedger::AR_IA_ACC_ROLLOVER);

		UpdateLedger::update($action, $tmp_journals, $date, false, true, $internal);
	}
}

Database::commit();
?>