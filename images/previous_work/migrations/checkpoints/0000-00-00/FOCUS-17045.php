<?php
//Jira 17045

if (empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}

require_once("{$GLOBALS['staticpath']}/Finance/classes/pr/FloridaSalaryAdjustmentCodes.php");

Database::begin();


switch (Database::$type) {
	case 'mssql':
		$dateType  = "DATETIME";
		break;

	default:
		$dateType  = "TIMESTAMP WITHOUT TIME ZONE";
		break;
}

if (!Database::tableExists("gl_pr_florida_salary_adjustments")) {
	Database::query("
		CREATE TABLE gl_pr_florida_salary_adjustments (
			id BIGINT PRIMARY KEY,
			deleted int,
			staff_id BIGINT NOT NULL,
			staff_job_id BIGINT NOT NULL,
			fyear int NOT NULL,
			step_id bigint,
			effective_date {$dateType},
			code_1 bigint,
			code_amount_1 numeric,
			code_2 bigint,
			code_amount_2 numeric,
			code_3 bigint,
			code_amount_3 numeric,
			code_4 bigint,
			code_amount_4 numeric,
			code_5 bigint,
			code_amount_5 numeric
		)
	");

	Database::query("create index fl_sal_adj_staff_id on gl_pr_florida_salary_adjustments (staff_id)");
	Database::query("create index fl_sal_adj_staff_job_id on gl_pr_florida_salary_adjustments (staff_job_id)");
	Database::query("create index fl_sal_adj_fyear on gl_pr_florida_salary_adjustments (fyear)");
}
else
{
	if (!Database::columnExists('gl_pr_florida_salary_adjustments', 'code_amount_1')) {
		Database::createColumn('gl_pr_florida_salary_adjustments', 'code_amount_1', 'numeric');
	}

	if (!Database::columnExists('gl_pr_florida_salary_adjustments', 'step_id')) {
		Database::createColumn('gl_pr_florida_salary_adjustments', 'step_id', 'bigint');
	}

	if (!Database::columnExists('gl_pr_florida_salary_adjustments', 'code_amount_2')) {
		Database::createColumn('gl_pr_florida_salary_adjustments', 'code_amount_2', 'numeric');
	}

	if (!Database::columnExists('gl_pr_florida_salary_adjustments', 'code_amount_3')) {
		Database::createColumn('gl_pr_florida_salary_adjustments', 'code_amount_3', 'numeric');
	}

	if (!Database::columnExists('gl_pr_florida_salary_adjustments', 'code_amount_4')) {
		Database::createColumn('gl_pr_florida_salary_adjustments', 'code_amount_4', 'numeric');
	}

	if (!Database::columnExists('gl_pr_florida_salary_adjustments', 'code_amount_5')) {
		Database::createColumn('gl_pr_florida_salary_adjustments', 'code_amount_5', 'numeric');
	}
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_1')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_1', 'bigint');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_2')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_2', 'bigint');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_3')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_3', 'bigint');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_4')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_4', 'bigint');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_5')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_5', 'bigint');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_amount_1')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_amount_1', 'numeric');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_amount_2')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_amount_2', 'numeric');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_amount_3')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_amount_3', 'numeric');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_amount_4')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_amount_4', 'numeric');
}

if (!Database::columnExists('gl_pr_staff_job_incentive_pay', 'code_amount_5')) {
	Database::createColumn('gl_pr_staff_job_incentive_pay', 'code_amount_5', 'numeric');
}

if (!Database::tableExists("gl_pr_florida_salary_adjustment_codes")) {

	Database::query("
		CREATE TABLE gl_pr_florida_salary_adjustment_codes (
			id BIGINT PRIMARY KEY,
			code varchar(2),
			title varchar(500),
			deleted int,
			date_beg {$dateType},
			date_end {$dateType}
		)
	");

	Database::query("create index fl_sal_adj_code_id on gl_pr_florida_salary_adjustment_codes (id)");
}

$tmp = Database::get("select * from gl_pr_florida_salary_adjustment_codes");
if(count($tmp) == 0)
{
	$init_values = array(
		"A"=>"Instructional or school administrative employee rated as highly effective",
		"B"=>"Instructional or school administrative employee rated as effective",
		"C"=>"Cost-of-living adjustment",
		"D"=>"Salary adjustment for salary schedule step",
		"E"=>"Advanced degree value that is part of the base salary for employees hired prior to July 1, 2011",
		"F"=>"Other salary adjustment"
	);

	foreach($init_values as $code=>$title)
	{
		$new = new FloridaSalaryAdjustmentCodes;
		$new
			->setCode($code)
			->setTitle($title)
			->persist();
	}
}

Database::commit();
return true;
