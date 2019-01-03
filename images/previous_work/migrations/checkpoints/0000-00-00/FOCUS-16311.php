<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

//Data for custom fields - adding to Employee Summary category
$es_cat = CustomFieldCategory::getOne("source_class = 'FocusUser' AND title = 'Employee Summary'");

if (!$es_cat)
	$es_cat = (new CustomFieldCategory())
		->setSourceClass("FocusUser")
		->setSortOrder(0)
		->setDefaultProfilesView("[\"1\"]")
		->setDefaultProfilesEdit("[\"1\"]")
		->setSis(1)
		->setErp(1)
		->setTitle("Employee Summary")
		->persist();

$w4status        = FocusUser::getFieldByAlias("w4_status");
$w4statusid      = $w4status["id"];
$w4status        = $w4status["column_name"];
$w4allow         = FocusUser::getFieldByAlias("w4_allowances");
$w4allow         = $w4allow["column_name"];
$w4addn          = FocusUser::getFieldByAlias("w4_additional");
$w4addn_id       = $w4addn["id"];
$w4addn          = $w4addn["column_name"];
$w4exempt        = FocusUser::getFieldByAlias("w4_exempt");
$w4exempt        = $w4exempt["column_name"];
$w4options       = [];
$tmp             = CustomFieldSelectOption::getAll("source_class = 'CustomField' AND source_id = {$w4statusid}");

if (empty($tmp))
	throw new Exception("W4 Status must have values set up in order to proceed.");

foreach ($tmp as $t)
	$w4options[] = sprintf("WHEN u.%s = %d THEN '%s'", $w4status, $t->getID(), $t->getLabel());

$w4options       = implode(" ", $w4options);
$w4addn_fmt      = db_to_char("COALESCE(CAST(u.{$w4addn} AS NUMERIC(9,2)), 0)", "0.00");
$w4allow_fmt     = db_to_char("COALESCE(CAST(u.{$w4allow} AS NUMERIC(9,2)), 0)", "0.00");
$sjp_sdate_fmt   = db_to_char("sjp.date_starting", "MM/DD/YYYY");
$sjp_edate_fmt   = db_to_char("sjp.date_ending", "MM/DD/YYYY");
$hours_day_fmt   = db_to_char("sjp.hours_per_day", "0.00");
$contr_hrs_fmt   = db_to_char("ROUND(w.contract_hours_per_day, 2)", "90.00");
$contr_days_fmt  = db_to_char("ROUND(w.contract_days, 0)", "990");
$contr_hrly_fmt  = db_to_char("ROUND(w.contract_hourly_rate, 2)", "9990.00");
$contr_daily_fmt = db_to_char("ROUND(w.contract_daily_pay, 2)", "9990.00");
$contr_per_fmt   = db_to_char("ROUND(w.contract_pay_period, 2)", "999,990.00");
$contr_wages_fmt = db_to_char("ROUND(w.contract_wages, 2)", "9,999,990.00");
$contr_ann_fmt   = db_to_char("ROUND(w.contract_annualized_earnings, 2)", "9,999,990.00");
$w_sdate_fmt     = db_to_char("w.date_start", "MM/DD/YYYY");
$w_edate_fmt     = db_to_char("w.date_end", "MM/DD/YYYY");
$sjs_sdate_fmt   = db_to_char("sjs.beg_date", "MM/DD/YYYY");
$sjs_edate_fmt   = db_to_char("sjs.end_date", "MM/DD/YYYY");
$paid_fmt        = db_to_char("
	COALESCE(
	   ROUND(
	      (
	         SELECT SUM(hrw1.wages)
	         FROM   gl_pr_history_run_wages hrw1
	         WHERE  hrw1.wage_type IN (
	                   'Contract Closing', 'Contract Dockage', 'Daily', 'Pay Adjustment', 'Performance', 'Reg Hourly',
	                   'Retro Pay', 'Salary', 'Sick Leave Dockage', 'Short Hours'
	         )
	         AND    hrw1.fyear = w.fyear
	         AND    hrw1.staff_job_id = sj.id
	         AND    hrw1.status = 'F'
	         AND    hrw1.date_transaction BETWEEN COALESCE(w.date_start, '1970-01-01') AND COALESCE(w.date_end, '2099-12-31')
	         AND    EXISTS (
	                   SELECT 'x'
	                   FROM   gl_ba_checks c1
	                   WHERE  c1.module = 'pr'
	                   AND    c1.vendor_id = hrw1.staff_id
	                   AND    c1.run_control_id = hrw1.run_id
	                   AND    c1.void_flag IS NULL
	                )
	      ),
	      2
	   ),
	   0.00
	)",
	"9,999,990.00"
);
$adj_fmt         = db_to_char("
	COALESCE(
	   ROUND(
	      (
	         SELECT SUM(hrw2.wages)
	         FROM   gl_pr_history_run_wages hrw2
	         JOIN   gl_pr_run_control_adjustments rca2 ON rca2.id = hrw2.adjustment_id
	         JOIN   gl_pr_adjustment_codes ac2 ON ac2.id = rca2.adjustment_code_id
	         WHERE  ac2.applies_to_contract = 'Y'
	         AND    hrw2.fyear = w.fyear
	         AND    hrw2.staff_job_id = sj.id
	         AND    hrw2.status = 'F'
	         AND    hrw2.date_transaction BETWEEN COALESCE(w.date_start, '1970-01-01') AND COALESCE(w.date_end, '2099-12-31')
	         AND    EXISTS (
	                   SELECT 'x'
	                   FROM gl_ba_checks c1
	                   WHERE c1.module = 'pr'
	                   AND c1.vendor_id = hrw2.staff_id
	                   AND c1.run_control_id = hrw2.run_id
	                   AND c1.void_flag IS NULL
	                )
	      ),
	      2
	   ),
	   0.00
	)",
	"9,999,990.00"
);
$remaining_fmt   = db_to_char("
	ROUND(
	   w.contract_wages -
	   COALESCE(
	      (
	         SELECT SUM(hrw3.wages)
	         FROM   gl_pr_history_run_wages hrw3
	         WHERE  hrw3.wage_type IN (
	                   'Contract Closing', 'Contract Dockage', 'Daily', 'Pay Adjustment', 'Performance', 'Reg Hourly',
	                   'Retro Pay', 'Salary', 'Sick Leave Dockage', 'Short Hours'
	                )
	         AND    hrw3.fyear = w.fyear
	         AND    hrw3.staff_job_id = sj.id
	         AND    hrw3.status = 'F'
	         AND    hrw3.date_transaction BETWEEN COALESCE(w.date_start, '1970-01-01') AND COALESCE(w.date_end, '2099-12-31')
	         AND    EXISTS (
	                   SELECT 'x'
	                   FROM gl_ba_checks c3
	                   WHERE c3.module = 'pr'
	                   AND c3.vendor_id = hrw3.staff_id
	                   AND c3.run_control_id = hrw3.run_id
	                   AND c3.void_flag IS NULL
	                )
	      ),
	      0.00
	   ) -
	   COALESCE(
	      (
	         SELECT SUM(hrw4.wages)
	         FROM   gl_pr_history_run_wages hrw4
	         JOIN   gl_pr_run_control_adjustments rca4 ON rca4.id = hrw4.adjustment_id
	         JOIN   gl_pr_adjustment_codes ac4 ON ac4.id = rca4.adjustment_code_id
	         WHERE  ac4.applies_to_contract = 'Y'
	         AND    hrw4.fyear = w.fyear
	         AND    hrw4.staff_job_id = sj.id
	         AND    hrw4.status = 'F'
	         AND    hrw4.date_transaction BETWEEN COALESCE(w.date_start, '1970-01-01') AND COALESCE(w.date_end, '2099-12-31')
	         AND    EXISTS (
	                   SELECT 'x'
	                   FROM gl_ba_checks c4
	                   WHERE c4.module = 'pr'
	                   AND c4.vendor_id = hrw4.staff_id
	                   AND c4.run_control_id = hrw4.run_id
	                   AND c4.void_flag IS NULL
	                )
	      ),
	      0.00
	   ),
	   2
	)",
	"9,999,990D00"
);
$pays_fmt        = db_to_char("ROUND(sjs.number_of_pays, 0)", "90");
$period_fmt      = db_to_char("ROUND(sjs.period_pay, 2)", "99,990.00");
$total_fmt       = db_to_char("ROUND(sjs.total_pay, 2)", "9,999,990.00");

$now             = Database::$type == "postgres" ? "NOW()" : "GETDATE()";
$es_cat_id       = $es_cat->getID();
$es_fields       = [
	[
		'title'          => 'Employee Identification Number',
		'column'         => 'es_ein',
		'type'           => 'computed',
		"computed_query" => "
			SELECT staff_id, RIGHT(CONCAT('00000000', ein), 8) AS value
			FROM   users
			WHERE  deleted IS NULL"
	],
	[
		'title'          => 'Full Name',
		'column'         => 'es_fullname',
		'type'           => 'computed',
		"computed_query" => "
			SELECT staff_id, UPPER(CONCAT(last_name, ', ', first_name, ' ', LEFT(middle_name, 1))) AS value
			FROM   users
			WHERE  deleted IS NULL"
	],
	[
		'title'          => 'W4 Information',
		'column'         => 'es_w4',
		'type'           => 'computed',
		"computed_query" => "
		   SELECT u.staff_id,
		          CONCAT(
		             'Filing ',
		             CASE {$w4options} ELSE 'Unknown' END,
		             ', ',
		             {$w4allow_fmt},
		             ' Allowances, +',
		             {$w4addn_fmt},
		             CASE WHEN u.{$w4exempt} IS NULL THEN '' ELSE ', (Exempt)' END
		          ) AS value
		   FROM   users u
		   WHERE  u.deleted IS NULL"
	],
	[
		'title'          => 'Active Positions',
		'column'         => 'es_active_pos',
		'type'           => 'computed_table',
		"computed_query" => "
			SELECT    sjp.staff_id, CONCAT(sj.staff_job_index, CASE WHEN sjp.staffs_primary_position = 'Y' THEN ' [Primary]' ELSE '' END) AS \"Group\",
			          CONCAT(f.code, ' ', f.name) AS \"Facility\", CONCAT(p.code, ' ', p.title) AS \"Position\",
			          CONCAT(j.job_number, ' ', j.title) AS \"Job Title\", {$sjp_sdate_fmt} AS \"Starting\", {$sjp_edate_fmt} AS \"Ending\",
			          {$hours_day_fmt} AS \"Hours Per Day\"
			FROM      gl_pr_staff_job_positions sjp
			JOIN      gl_pr_staff_jobs sj ON sj.id = sjp.staff_job_id
			JOIN      gl_pr_positions p ON p.id = sjp.position_id
			LEFT JOIN gl_facilities f ON f.id = sjp.responsible_facility
			LEFT JOIN gl_pr_jobs_local j ON j.id = sjp.job_id
			WHERE     {$now} BETWEEN COALESCE(sjp.date_starting, '1970-01-01') AND COALESCE(sjp.date_ending, '2099-12-31')
			AND       sj.deleted IS NULL"
	],
	[
		'title'          => 'Salary',
		'column'         => 'es_salary',
		'type'           => 'computed_table',
		"computed_query" => "
			SELECT w.staff_id, w.fyear AS \"Fiscal Year\", sj.staff_job_index AS \"Group\", sp.code AS \"Step\",
			       LTRIM({$contr_hrs_fmt}) AS \"Hrs/Day\",
			       LTRIM({$contr_days_fmt}) AS \"Days\",
			       CONCAT('$', LTRIM({$contr_hrly_fmt})) AS \"Hourly\",
			       CONCAT('$', LTRIM({$contr_daily_fmt})) AS \"Daily\",
			       CONCAT('$', LTRIM({$contr_per_fmt})) AS \"Period\",
			       CONCAT('$', LTRIM({$contr_wages_fmt})) AS \"Contract\",
			       CONCAT('$', LTRIM({$contr_ann_fmt})) AS \"Annualized\",
			       {$w_sdate_fmt} AS \"Starting\",
			       {$w_edate_fmt} AS \"Ending\",
			       CONCAT('$', LTRIM({$paid_fmt})) AS \"Paid\",
			       CONCAT('$', LTRIM({$adj_fmt})) AS \"Adj\",
			       CONCAT('$', LTRIM({$remaining_fmt})) AS \"Remaining\"
			FROM   gl_pr_current_fyear_job_wages w
			JOIN   gl_pr_slot_pay sp ON sp.id = w.slot_pay_id
			JOIN   gl_pr_staff_jobs sj ON sj.id = w.staff_job_id
			WHERE  w.fyear = {syear}
			AND    w.supplement = 'N'
			AND    w.deleted IS NULL
			AND    sj.deleted IS NULL"
	],
	[
		'title'          => 'Supplements',
		'column'         => 'es_supplements',
		'type'           => 'computed_table',
		"computed_query" => "
		   SELECT    sjs.staff_id,
		             sjs.fyear AS \"Fiscal Year\",
		             sj.staff_job_index AS \"Group\",
		             CONCAT(f.code, ' ', f.name) AS \"Facility\",
		             CONCAT(p.code, ' ', p.title) AS \"Position\",
		             CONCAT(sp.code, ' ', sp.title) AS \"Step\",
		             {$sjs_sdate_fmt} AS \"Starting\",
		             {$sjs_edate_fmt} AS \"Ending\",
		             {$pays_fmt} AS \"Pays\",
		             {$period_fmt} AS \"Period\",
		             {$total_fmt} AS \"Total\"
		   FROM      gl_pr_staff_job_supplements sjs
		   JOIN      gl_pr_staff_jobs sj ON sj.id = sjs.staff_job_id
		   JOIN      gl_pr_slot_pay sp ON sp.id = sjs.slot_pay_id
		   LEFT JOIN gl_facilities f ON f.id = sjs.facility_id
		   LEFT JOIN gl_pr_positions p ON p.id = sjs.position_id
		   WHERE     sjs.fyear = {syear}
		   AND       sjs.deleted IS NULL
		   AND       sj.deleted IS NULL"
	],
];

$access_profiles = Database::get("SELECT profile_id FROM permission WHERE \"key\" = 'hr::demographic'");

foreach ($es_fields as $sort_increment => $field)
{
	if (empty(FocusUser::getFieldByAlias($field['column'])))
	{
		try
		{
			$cf = CustomField::getOne("alias = '{$field["column"]}' AND source_class = 'FocusUser'");

			if ($cf)
			{
				if ($cf->getComputedQuery() != $field['computed_query'])
					$cf->setComputedQuery($field['computed_query'])->persist();
			}
			else
				$cf = (new CustomField())
					->setTitle($field['title'])
					->setAlias($field['column'])
					->setType($field['type'])
					->setComputedQuery($field['computed_query'])
					->setSourceClass('FocusUser')
					->setRequiresAuthentication(1)
					->setSystem(1)
					->persist();
		}
		catch (Exception $e)
		{
			printf("<DIV>An error has been encountered trying to create a custom field entry</DIV>");
			printf("<DIV STYLE=\"padding-left:10px\"><B>Title: </B>%s</DIV>", $field["title"]);
			printf("<DIV STYLE=\"padding-left:10px\"><B>Column: </B>%s</DIV>", $field["column"]);
			printf("<DIV STYLE=\"padding-left:10px\"><B>Type: </B>%s</DIV>", $field["type"]);
			printf("<DIV STYLE=\"padding-left:10px\"><B>Computed Query: </B>%s</DIV>", str_replace("\n", " ", $field["computed_query"]));

			foreach (["status", "allowances", "additional", "exempt"] as $x)
			{
				$y = FocusUser::getFieldByAlias("w4_{$x}");
				printf("<DIV><B>w4_{$x} Custom Field</B>%s<DIV>", print_r($y, true));
			}

			printf("<BR><BR>%s", $e->getMessage());
			printf("<BR><BR>%s", str_replace("\n", "<BR>", $e->getTraceAsString()));

			return false;
		}

		$field_id = $cf->getId();

		foreach ($access_profiles as $access_profile)
		{
			Database::query("INSERT INTO permission (profile_id, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_view')");
			Database::query("INSERT INTO permission (profile_id, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_edit')");
		}

		//Join to category - add to the bottom
		$cfjc = (new CustomFieldJoinCategory())
			->setCategoryId($es_cat_id)
			->setFieldId($field_id)
			->setSortOrder($sort_increment + 1)
			->persist();
	}
}

$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();

Database::commit();

return true;
