<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$params = [
	"old_permission" => "pr::w2s"
];
$sql    =
	"INSERT INTO
		permission
			(id, \"key\", profile_id)
	SELECT
		{{next:permission_seq}},
		'menu::pr_w2_report',
		profile_id
	FROM
		permission
	WHERE
		\"key\" = :old_permission";
$sql    = Database::preprocess($sql);

Database::query($sql, $params);

$sql =
	"DELETE FROM
		permission
	WHERE
		\"key\" = :old_permission";

Database::query($sql, $params);

if (!Database::tableExists("gl_pr_w2_adjustments")) {
	$sql =
		"CREATE TABLE gl_pr_w2_adjustments (
			id BIGINT PRIMARY KEY,
			staff_id BIGINT NOT NULL,
			year BIGINT NOT NULL,
			wages NUMERIC(28,10),
			federal_tax_withheld NUMERIC(28,10),
			social_security_wages NUMERIC(28,10),
			social_security_tax_withheld NUMERIC(28,10),
			medicare_wages NUMERIC(28,10),
			medicare_tax_withheld NUMERIC(28,10),
			dependent_care_benefits NUMERIC(28,10),
			nonqualified_plans_457 NUMERIC(28,10),
			nonqualified_plans_non_457 NUMERIC(28,10),
			plan_401a_frs NUMERIC(28,10),
			plan_401k NUMERIC(28,10),
			plan_403b_elective NUMERIC(28,10),
			plan_403b_mandatory NUMERIC(28,10),
			plan_403b_roth NUMERIC(28,10),
			plan_457b NUMERIC(28,10),
			hsa NUMERIC(28,10),
			excess_life_insurance NUMERIC(28,10),

			UNIQUE(staff_id, year)
		)";

	Database::query($sql);
}

Database::commit();
return true;
?>