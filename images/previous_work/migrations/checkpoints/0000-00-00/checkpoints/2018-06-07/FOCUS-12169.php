<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$current_year = GLLedger::getFiscalYear();

foreach (FiscalYear::range(2) as $year) {
	if ($year === $current_year) {
		continue;
	}

	$fiscal_start = "{$year}-" . FISCAL_START_MONTH . "-01";
	$fiscal_end   = date("Y-m-d 23:59:59", strtotime("+1 year -1 day", strtotime($fiscal_start)));
	$next_year    = $year + 1;
	$sql          =
		"UPDATE
			gl_ba_checks
		SET
			fiscal_year = :current_year
		WHERE
			fiscal_year = :next_year AND
			check_date BETWEEN :start_date AND :end_date";
	$params       = [
		"current_year" => $year,
		"next_year"    => intval($next_year),
		"start_date"   => $fiscal_start,
		"end_date"     => $fiscal_end
	];

	Database::query($sql, $params);
}

Database::commit();
?>
