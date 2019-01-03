<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_pr_positions")) {
	return false;
}

if (!Database::columnExists("gl_pr_positions", "historical_id")) {
	Database::begin();
	Database::createColumn("gl_pr_positions", "historical_id", "BIGINT");
	Database::commit();
}

$sql =
	"SELECT 
		code, 
		facility_id,
		job_id,
		pay_type_id
	FROM 
		gl_pr_positions 
	GROUP BY 
		code, 
		facility_id,
		job_id,
		pay_type_id";

$positions = Database::get($sql);

Database::begin();

foreach ($positions as $position) {
	$code     = $position["CODE"];
	$facility = $position["FACILITY_ID"];
	$job      = ($position["JOB_ID"]) ?: 0;
	$payType  = ($position["PAY_TYPE_ID"]) ?: 0;
	$where    = [
		"code = '{$code}'",
		"COALESCE(job_id, 0) = {$job}",
		"COALESCE(pay_type_id, 0) = {$payType}"
	];

	if (!$code) {
		continue;
	}

	$historicalId = Positions::createHistoricalId();
	
	if ($facility) {
		$where[] = "facility_id = {$facility}";
	} else {
		$where[] = "COALESCE(facility_id, 0) = 0";
	}

	$where = implode(" AND ", $where);

	$sql = 
		"UPDATE 
			gl_pr_positions
		SET 
			historical_id = {$historicalId}
		WHERE 
			{$where}";

	Database::query($sql);
}

Database::commit();
return true;
?>