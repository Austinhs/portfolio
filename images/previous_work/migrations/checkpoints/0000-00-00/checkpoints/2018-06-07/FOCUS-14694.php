<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$fields = [
	"bargaining_unit_id"            => "bigint",
	"employee_equal_opportunity_id" => "bigint",
	"ian"                           => "varchar",
	"pay_method"                    => "varchar",
	"qualifies_for_overtime"        => "varchar",
	"workers_comp_insurance_group"  => "varchar",
	"status"                        => "varchar",
	"code"                          => "varchar"
];

foreach ($fields as $field => $type)
	if (!Database::columnExists("gl_pr_jobs_state", $field))
		Database::createColumn("gl_pr_jobs_state", $field, $type);

Database::begin();

Database::query("UPDATE gl_pr_jobs_state SET status = 'A' WHERE status IS NULL");
Database::query("UPDATE gl_pr_jobs_state SET code = local_id WHERE code IS NULL");

Database::commit();
