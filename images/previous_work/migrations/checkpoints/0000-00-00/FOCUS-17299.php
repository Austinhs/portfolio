<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

//Data for custom fields - adding to Employee Summary category
$es_cat = CustomFieldCategory::getOne("source_class = 'FocusUser' AND title = 'Employee Summary'");

if (!$es_cat) {
	$es_cat = (new CustomFieldCategory())
		->setSourceClass("FocusUser")
		->setSortOrder(0)
		->setDefaultProfilesView("[\"1\"]")
		->setDefaultProfilesEdit("[\"1\"]")
		->setSis(1)
		->setErp(1)
		->setTitle("Employee Summary")
		->persist();
}

$sjp_sdate_fmt = db_to_char("sjp.date_starting", "MM/DD/YYYY");
$sjp_edate_fmt = db_to_char("sjp.date_ending", "MM/DD/YYYY");
$hours_day_fmt = db_to_char("sjp.hours_per_day", "0.00");
$sjs_sdate_fmt = db_to_char("sjs.beg_date", "MM/DD/YYYY");
$sjs_edate_fmt = db_to_char("sjs.end_date", "MM/DD/YYYY");
$pays_fmt      = db_to_char("ROUND(sjs.number_of_pays, 0)", "90");
$period_fmt    = db_to_char("ROUND(sjs.period_pay, 2)", "99,990.00");
$total_fmt     = db_to_char("ROUND(sjs.total_pay, 2)", "9,999,990.00");
$now           = Database::$type == "postgres" ? "NOW()" : "GETDATE()";
$es_cat_id     = $es_cat->getID();
$es_fields     = [
	[
		'title'          => 'Active Positions',
		'column'         => 'es_active_pos',
		'type'           => 'computed_table',
		"computed_query" => "
			SELECT    sjp.staff_id,
			          CONCAT(sj.staff_job_index, CASE WHEN sjp.staffs_primary_position = 'Y' THEN ' [Primary]' ELSE '' END) AS \"Group\",
			          CONCAT(f.code, ' ', f.name) AS \"Facility\",
			          CONCAT(p.code, ' ', p.title) AS \"Position\",
			          CONCAT(j.job_number, ' ', j.title) AS \"Title\",
			          TO_CHAR(sjp.date_starting,'MM/DD/YYYY') AS \"Starting\",
			          TO_CHAR(sjp.date_ending,'MM/DD/YYYY') AS \"Ending\",
			          to_char(sjp.hours_per_day,'0.00000') AS \"Hours\"
			FROM      gl_pr_staff_job_positions sjp
			JOIN      gl_pr_staff_jobs sj ON sj.id = sjp.staff_job_id
			JOIN      gl_pr_positions p ON p.id = sjp.position_id
			LEFT JOIN gl_facilities f ON f.id = sjp.responsible_facility
			LEFT JOIN gl_pr_jobs_local j ON j.id = sjp.job_id
			WHERE     sjp.staff_id = {staff_id}
			AND       NOW()
			          BETWEEN COALESCE(sjp.date_starting, CAST(CONCAT(CAST(sjp.fyear AS VARCHAR), '-07-01') AS DATE))
			          AND     COALESCE(sjp.date_ending, CAST(CONCAT(CAST(sjp.fyear + 1 AS VARCHAR), '-06-30') AS DATE))
			AND       sj.deleted IS NULL"
	],
	[
		'title'          => 'Supplements',
		'column'         => 'es_supplements',
		'type'           => 'computed_table',
		"computed_query" => "
			WITH vars AS (
			   SELECT CAST(fy.year AS NUMERIC) AS fyear
			   FROM   gl_fiscal_year fy
			   WHERE  CURRENT_TIMESTAMP BETWEEN fy.year_start AND fy.year_end
			)
			SELECT    sjs.staff_id,
			          sjs.fyear AS \"Fiscal Year\",
			          sj.staff_job_index AS \"Group\",
			          CONCAT(f.code, ' ', f.name) AS \"Facility\",
			          CONCAT(p.code, ' ', p.title) AS \"Position\",
			          CONCAT(sp.code, ' ', sp.title) AS \"Step\",
			          TO_CHAR(sjs.beg_date, 'MM/DD/YYYY') AS \"Starting\",
			          TO_CHAR(sjs.end_date, 'MM/DD/YYYY') AS \"Ending\",
			          CAST(ROUND(sjs.number_of_pays, 0) AS NUMERIC(2,0)) AS \"Pays\",
			          CAST(ROUND(sjs.period_pay, 2) AS NUMERIC(5,2)) AS \"Period\",
			          CAST(ROUND(sjs.total_pay, 2) AS NUMERIC(7,2)) AS \"Total\"
			FROM      gl_pr_staff_jobs sj
			JOIN      gl_pr_staff_job_positions sjp ON sj.id = sjp.staff_job_id
			JOIN      gl_pr_staff_job_supplements sjs ON sjp.staff_job_id = sjs.staff_job_id
			JOIN      gl_pr_slot_pay sp ON sjs.slot_pay_id = sp.id
			LEFT JOIN gl_facilities f ON sjs.facility_id = f.id
			LEFT JOIN gl_pr_positions p ON sjp.position_id = p.id
			WHERE     COALESCE(sj.termination_date, CURRENT_TIMESTAMP) >= CURRENT_TIMESTAMP
			AND       sjp.fyear = (SELECT fyear FROM vars)
			AND       sjp.staff_id = {staff_id}
			AND       COALESCE(sjp.date_ending, CURRENT_TIMESTAMP) >= CURRENT_TIMESTAMP"
	],
];

$access_profiles = Database::get("SELECT profile_id FROM permission WHERE \"key\" = 'hr::demographic'");

foreach ($es_fields as $sort_increment => $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		try {
			$cf = CustomField::getOne("alias = '{$field["column"]}' AND source_class = 'FocusUser'");

			if ($cf) {
				if ($cf->getComputedQuery() != $field['computed_query'])
					$cf->setComputedQuery($field['computed_query'])->persist();
			} else {
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
		} catch (Exception $e) {
			printf("<DIV>An error has been encountered trying to create a custom field entry</DIV>");
			printf("<DIV STYLE=\"padding-left:10px\"><B>Title: </B>%s</DIV>", $field["title"]);
			printf("<DIV STYLE=\"padding-left:10px\"><B>Column: </B>%s</DIV>", $field["column"]);
			printf("<DIV STYLE=\"padding-left:10px\"><B>Type: </B>%s</DIV>", $field["type"]);
			printf("<DIV STYLE=\"padding-left:10px\"><B>Computed Query: </B>%s</DIV>", str_replace("\n", " ", $field["computed_query"]));

			foreach (["status", "allowances", "additional", "exempt"] as $x) {
				$y = FocusUser::getFieldByAlias("w4_{$x}");
				printf("<DIV><B>w4_{$x} Custom Field</B>%s<DIV>", print_r($y, true));
			}

			printf("<BR><BR>%s", $e->getMessage());
			printf("<BR><BR>%s", str_replace("\n", "<BR>", $e->getTraceAsString()));

			die();
		}

		$field_id = $cf->getId();

		foreach ($access_profiles as $access_profile) {
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
