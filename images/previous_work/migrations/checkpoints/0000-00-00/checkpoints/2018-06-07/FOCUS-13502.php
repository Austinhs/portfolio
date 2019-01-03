<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$district_facility_id = Facility::getDistrictFacilityId();
$params               = [
	"district_facility_id" => $district_facility_id
];
$sql                  =
	"UPDATE
		gl_ba_checks
	SET
		facility_id = b.facility_id
	FROM
		gl_batches b
	WHERE
		b.batch_id = gl_ba_checks.batch_id AND
		gl_ba_checks.internal = 1 AND
		(
			b.facility_id != gl_ba_checks.facility_id OR
			gl_ba_checks.facility_id = :district_facility_id
		)";

Database::query($sql, $params);
Database::commit();
return true;
?>