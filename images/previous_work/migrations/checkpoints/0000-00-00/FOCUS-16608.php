<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$reasons = [
	"A" => "Retirement",
	"B" => "Resignation for employment in education in Florida",
	"C" => "Resignation for employment outside of education",
	"D" => "Resignation with prejudice",
	"E" => "Resignation for other personal reasons",
	"F" => "Staff reduction",
	"G" => "Dismissal due to findings by the board related to charges",
	"H" => "Death",
	"I" => "Contract expired",
	"J" => "Reason not known",
	"K" => "Disabled",
	"L" => "Resignation for employment in education outside Florida",
	"M" => "Contract not renewed, due to less than satisfactory performance",
	"N" => "Dismissal during probationary period.",
	"O" => "Job Abandonment",
	"P" => "Classroom teachers or principals who were dismissed for ineffective performance as demonstrated through the district''s evaluation system.",
	"Z" => "Not applicable. Include temporary employees here.",
];
$valid   = array_keys($reasons);
$remove  = [];
$create  = [];
$live    = [];
$cf      = FocusUser::getFieldByAlias("separation_reason");
$next    = Database::nextSql("custom_field_select_options_seq");

if (!$cf) {
	return true;
}

$cf_id   = $cf["id"];
$res     = Database::get("
	SELECT cfso.code
	FROM   custom_field_select_options cfso
	JOIN   custom_fields cf ON cf.id = cfso.source_id
	WHERE  cfso.source_class = 'CustomField'
	AND    cfso.source_id = {$cf_id}"
);

foreach ($res as $r) {
	$live[$r["CODE"]] = true;
}

foreach ($live as $k => $v) {
	if (!isset($reasons[$k])) {
		$remove[] = "'{$k}'";
	}
}

foreach ($reasons as $k => $v) {
	if (!isset($live[$k])) {
		$create[$k] = true;
	}
}

if (!empty($remove)) {
	Database::query("DELETE FROM custom_field_select_options WHERE source_class = 'CustomField' AND source_id = {$cf_id} AND code IN ({$remove})");
}

if (!empty($create)) {
	foreach ($reasons as $k => $v) {
		if (isset($create[$k])) {
			Database::query("INSERT INTO custom_field_select_options (id, source_class, source_id, code, label) VALUES ({$next}, 'CustomField', {$cf_id}, '{$k}', '{$v}')");
		}
	}
}

StaffJobPositions::markSeparation();

return true;
