<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$repeat = Database::$type == "postgres" ? "REPEAT" : "REPLICATE";

$startDateMY  = db_to_char("CAST('{START_DATE}' AS date)", "MMYYYY");
$birthDateMDY = db_to_char("u.birth_date", "MMDDYYYY");
$hireDateMDY  = db_to_char("COALESCE(u.continuous_employment_date, u.original_position_employment_date)", "MMDDYYYY");
$termDateMDY  = db_to_char("j.termination_date", "MMDDYYYY");
$startDateM   = db_to_char("CAST('{START_DATE}' AS date)", "MM");
$startDateY   = db_to_char("CAST('{START_DATE}' AS date)", "YYYY");

$sql = [
	"frs_deduction" => "
		SELECT   sd.staff_id,
		         dc.deduction_class AS class,
		         rp.code AS plan,
		         CASE WHEN ds.who_pays = 'D' THEN ds.id END AS deduction_id,
		         ds.retirement_plan,
		         ROW_NUMBER() OVER(
		            PARTITION BY sd.staff_id, dc.deduction_class, rp.code
		            ORDER BY sd.staff_id, dc.deduction_class, rp.code
		         ) AS rownum
		FROM     gl_pr_staff_deductions sd, gl_pr_deductions ds, gl_pr_deduction_classes dc, gl_pr_retirement_plans rp
		WHERE    sd.deduction_id = ds.id
		AND      ds.deduction_class_id = dc.id
		AND      dc.deduction_class = '{DCLASS}'
		AND      rp.id = ds.retirement_plan
		AND      sd.deleted IS NULL
		AND      sd.status IS NOT NULL
		AND      EXISTS (
		            SELECT 'x'
		            FROM   gl_pr_history_run_deductions h
		            WHERE  h.staff_id = sd.staff_id
		            AND    h.deduction_id = sd.deduction_id
		            AND    h.status = 'F'
		            AND    h.date_transaction BETWEEN '{START_DATE}' AND '{END_DATE}'
		         )
		ORDER BY sd.staff_id, dc.deduction_class, rp.code",
	"most_recent_job" => "
		SELECT w.staff_id,
		       w.job_id AS job_id,
		       w.primary_facility_id AS primary_facility_id,
		       pt.service_months AS service_months,
		       w.date_start AS date_start,
		       CASE WHEN j.termination_date <= '{END_DATE}' THEN j.termination_date ELSE NULL END AS termination_date,
		       COALESCE(w.contract_annualized_earnings, 0) AS contract_annualized_earnings,
		       CASE WHEN CAST(eeo.code AS INT) >= 21 AND CAST(eeo.code AS INT) <= 43 THEN 'Y' ELSE ' ' END AS eeo,
		       ROW_NUMBER() OVER(
		          PARTITION BY w.staff_id
		          ORDER BY (CASE WHEN COALESCE(j.termination_date, '2100-01-01') < '{START_DATE}' THEN 0 ELSE 1 END) DESC,
		                   COALESCE(j.job_code_primary, 'N') DESC,
		                   CASE WHEN CAST(eeo.code AS INT) >= 21 AND CAST(eeo.code AS INT) <= 43 THEN 'Y' ELSE 'Z' END ASC,
		                   w.date_start DESC,
		                   w.job_id
		       ) AS rownum
		FROM   gl_pr_current_fyear_job_wages w
		JOIN   gl_pr_staff_jobs j ON j.id = w.staff_job_id
		JOIN   gl_pr_jobs_local l ON j.job_id = l.id
		JOIN   gl_pr_equal_employment_opportunity_numbers eeo ON eeo.id = l.employee_equal_opportunity_id
		JOIN   gl_pr_pay_types_for_year pt ON pt.pay_type_id = w.pay_type_id AND pt.fyear = w.fyear
		WHERE  w.fyear = '{FISCAL_YEAR}'
		AND    w.deleted IS NULL
		AND    j.deleted IS NULL
		AND    EXISTS (
		          SELECT 'x'
		          FROM   gl_pr_history_run_deductions h, gl_pr_history_run_wages hw
		          WHERE  hw.staff_id = h.staff_id
		          AND    hw.run_id = h.run_id
		          AND    h.staff_id = w.staff_id
		          AND    h.date_transaction BETWEEN '{START_DATE}' AND '{END_DATE}'
		       )",
	"deduction_filled" => "
		SELECT   hd.staff_id,
		         ds.who_pays,
		         ds.retirement_plan,
		         CONCAT(
		            (CASE WHEN SUM(deduction) < 0 THEN '-' ELSE ' ' END),
		            RIGHT(CONCAT({$repeat}('0', 10), COALESCE(CAST(ROUND(ABS(SUM(deduction * 100)), 0) AS VARCHAR(20)), '0')), 10)
		         ) AS deduction
		FROM     gl_pr_history_run_deductions hd, gl_pr_run_controls c, gl_pr_deductions ds, gl_pr_deduction_classes dc
		WHERE    hd.status = 'F'
		AND      c.id = hd.run_id
		AND      c.check_date BETWEEN '{START_DATE}' AND '{END_DATE}'
		AND      dc.id = ds.deduction_class_id
		AND      dc.deduction_class = '{DCLASS}'
		AND      ds.id = hd.deduction_id
		GROUP BY hd.staff_id, ds.who_pays, ds.retirement_plan, hd.deduction_id",
	"employee_email" => "
		SELECT c.parent_id AS staff_id,
		       UPPER(cont_data) AS email,
		       ROW_NUMBER() OVER(PARTITION BY c.parent_id ORDER BY ct.sort_order) AS rownum
		FROM   gl_contact c
		JOIN   gl_contact_types ct ON ct.id = c.cont_type AND (LOWER(ct.title) LIKE '%e-mail%' OR LOWER(ct.title) LIKE '%email%')
		WHERE  c.parent_class = 'ERPUser'",
	"employee_addr" => "
		SELECT parent_id AS staff_id,
		       CONCAT(
		          LEFT(CONCAT(COALESCE(address1, ' '), {$repeat}(' ', 31)), 31),
		          LEFT(CONCAT(COALESCE(address2, ' '), {$repeat}(' ', 62)), 62),
		          LEFT(CONCAT(COALESCE(city, ' '), {$repeat}(' ', 25)), 25),
		          LEFT(CONCAT(COALESCE(state, ' '), {$repeat}(' ', 2)), 2),
		          LEFT(CONCAT(COALESCE(zipcode, ' '), {$repeat}(' ', 9)), 9)
		       ) AS address,
		       ROW_NUMBER() OVER(PARTITION BY parent_id ORDER BY id DESC) AS rownum
		FROM   gl_address a
		WHERE  primary_addr = 1
		AND    parent_class = 'ERPUser'",
	"annual_supps" => "
		SELECT   staff_id, SUM(COALESCE(annualized, total_pay)) AS annualized
		FROM     gl_pr_staff_job_supplements
		WHERE    deleted IS NULL
		AND      fyear = '{FISCAL_YEAR}'
		GROUP BY staff_id",
	"all_deductions" => "
		SELECT   hd.staff_id, SUM(deduction) AS deduction
		FROM     gl_pr_history_run_deductions hd,
		         gl_pr_run_controls c,
		         gl_pr_deductions ds,
		         gl_pr_deduction_classes dc
		WHERE    hd.status = 'F'
		AND      c.id = hd.run_id
		AND      c.check_date BETWEEN '{START_DATE}' AND '{END_DATE}'
		AND      dc.id = ds.deduction_class_id
		AND      dc.deduction_class = '{DCLASS}'
		AND      ds.id = hd.deduction_id
		AND      ds.who_pays = 'D'
		GROUP BY hd.staff_id",
	"ret_deductions" => "
		SELECT   hd.staff_id, ds.retirement_plan, SUM(deduction) AS deduction
		FROM     gl_pr_history_run_deductions hd,
		         gl_pr_run_controls c,
		         gl_pr_deductions ds,
		         gl_pr_deduction_classes dc
		WHERE    hd.status = 'F'
		AND      c.id = hd.run_id
		AND      c.check_date BETWEEN '{START_DATE}' AND '{END_DATE}'
		AND      dc.id = ds.deduction_class_id
		AND      dc.deduction_class = '{DCLASS}'
		AND      ds.id = hd.deduction_id
		AND      ds.who_pays = 'D'
		GROUP BY hd.staff_id, ds.retirement_plan",
	"tot_salary" => "
		SELECT    ct.staff_id,
		          dedret.retirement_plan,
		          CONCAT(
		             (CASE WHEN SUM(ct.gross_retirement) < 0 THEN '-' ELSE ' ' END),
		             RIGHT(
		                CONCAT(
		                   {$repeat}('0', 10),
		                   CAST(
		                      ROUND(
		                         ABS(
		                            SUM(ct.gross_retirement * 100) * (
		                               CASE
		                                  WHEN COALESCE(dedall.deduction, 0) > 0 THEN COALESCE(dedret.deduction, 0) / COALESCE(dedall.deduction, 0)
		                                  ELSE 1
		                               END
		                            )
		                         )
		                      )
		                      AS VARCHAR(11)
		                   )
		                ),
		                10
		             )
		          ) AS salary
		FROM      gl_ba_check_totals ct
		JOIN      gl_pr_run_controls rc ON rc.id = ct.run_id
		LEFT JOIN all_deductions dedall ON dedall.staff_id = ct.staff_id
		LEFT JOIN ret_deductions dedret ON dedret.staff_id = ct.staff_id
		WHERE     ct.gross_retirement > 0
		AND       rc.check_date BETWEEN '{START_DATE}' AND '{END_DATE}'
		GROUP BY  ct.staff_id, dedret.retirement_plan, dedall.deduction, dedret.deduction",
	"frsrows" => "
		SELECT    CAST('54007' AS TEXT) AS prefix,
		          COALESCE(u.custom_556, {$repeat}('0', 9)) AS ssn,
		          {$startDateMY} AS mmyear,
		          LEFT(
		             CONCAT(
		                CONCAT(u.last_name, COALESCE(CONCAT(' ', u.name_suffix), ''), ', ', u.first_name, ' ', COALESCE(u.middle_name, ' ')),
		                {$repeat}(' ', 31)
		             ),
		             31
		          ) AS name,
		          LEFT(CONCAT(COALESCE(f.plan, ' '), {$repeat}(' ', 2)), 2) AS plan,
		          RIGHT(CONCAT({$repeat}('0', 2), COALESCE(CAST(j.service_months AS VARCHAR(2)), '0')), 2) AS service_months,
		          CAST(' ' AS TEXT) AS filler1,
		          COALESCE(saltot.salary, ' 0000000000') AS salary,
		          COALESCE(dfe.deduction, ' 0000000000') AS retirement_contribution_employee,
		          COALESCE(dfd.deduction, ' 0000000000') AS retirement_contribution_employeer,
		          CAST(' 00000' AS TEXT) AS annual_hours,
		          RIGHT(CONCAT({$repeat}('0', 12), COALESCE(fac.code, '0')), 12) AS dept,
		          RIGHT(CONCAT(' ', COALESCE(FieldOptionCode(u.gender), ' ')), 1) AS gender,
		          LEFT(CONCAT(COALESCE({$birthDateMDY}, '0'), {$repeat}('0', 8)), 8) AS birth_date,
		          COALESCE(j.eeo, ' ') AS eeo,
		          CASE WHEN COALESCE(j.job_id, 999999) = 297165 THEN '     0000063000' ELSE {$repeat}(' ', 15) END AS filler3,
		          LEFT(CONCAT(COALESCE(ea.address, ' '), {$repeat}(' ', 129)), 129) AS address,
		          COALESCE({$hireDateMDY}, {$repeat}('0', 8)) AS continuous_employment_date,
		          COALESCE({$termDateMDY}, {$repeat}(' ', 8)) AS termination_date,
		          CONCAT(
		             ' ',
		             COALESCE(
		                RIGHT(
		                   CONCAT(
		                      {$repeat}('0', 10),
		                      CAST(ROUND((COALESCE(j.contract_annualized_earnings, 0) + COALESCE(ans.annualized, 0)) * 100) AS VARCHAR(20))
		                   ),
		                   10
		                ),
		                {$repeat}('0', 15)
		             )
		          ) AS contract_annualized_earnings,
		          CAST(' 0000000000' AS TEXT) AS col_2,
		          CAST(' 0000000000' AS TEXT) AS col_3,
		          CAST(' ' AS TEXT) AS filler_4,
		          CAST(' 0000000000' AS TEXT) AS col_4,
		          CAST(' 0000000000' AS TEXT) AS col_5,
		          CAST(' 0000000000' AS TEXT) AS col_6,
		          CAST(' 0000000000' AS TEXT) AS col_7,
		          CAST(' ' AS TEXT) AS filler_5,
		          CAST(' 0000000000' AS TEXT) AS col_8,
		          CAST(' 0000000000' AS TEXT) AS col_9,
		          CAST(' 0000000000' AS TEXT) AS col_10,
		          CAST(' 0000000000' AS TEXT) AS col_11,
		          RIGHT(CONCAT(' ', COALESCE(u.exempt_from_public_record, ' ')), 1) AS public_records_exempt,
		          CAST(' ' AS TEXT) AS filler_6,
		          LEFT(CONCAT(COALESCE(ee.email, ' '), {$repeat}(' ', 60)), 60) AS email,
		          CAST({$repeat}(' ', 15) AS TEXT) as col_12
		FROM      users u
		JOIN      frs_deduction f ON f.staff_id = u.staff_id AND f.rownum = 1
		JOIN      most_recent_job j ON j.staff_id = u.staff_id AND j.rownum = 1
		JOIN      gl_facilities fac ON fac.id = j.primary_facility_id
		LEFT JOIN deduction_filled dfe ON dfe.staff_id = u.staff_id AND dfe.who_pays = 'E' AND dfe.retirement_plan = f.retirement_plan
		LEFT JOIN deduction_filled dfd ON dfd.staff_id = u.staff_id AND dfd.who_pays = 'D' AND dfe.retirement_plan = f.retirement_plan
		LEFT JOIN employee_email ee ON ee.staff_id = u.staff_id AND ee.rownum = 1
		LEFT JOIN employee_addr ea ON ea.staff_id = u.staff_id AND ea.rownum = 1
		LEFT JOIN annual_supps ans ON ans.staff_id = u.staff_id
		LEFT JOIN tot_salary saltot ON saltot.staff_id = u.staff_id",
	"adjrows" => "
		SELECT    CAST('54007' AS TEXT) AS prefix,
		          COALESCE(u.custom_556, {$repeat}('0', 9)) AS ssn,
		          COALESCE(adj.adjustment_period, {$repeat}('0', 6)) AS mmyear,
		          LEFT(
		             CONCAT(
		                u.last_name,
		                (CASE WHEN u.name_suffix IS NOT NULL THEN CONCAT(' ', u.name_suffix) ELSE '' END),
		                ', ',
		                u.first_name,
		                ' ',
		                COALESCE(u.middle_name, ' '),
		                {$repeat}(' ', 31)
		             ),
		             31
		          ) AS name,
		          LEFT(CONCAT(COALESCE(adj_plan.code, ' '), {$repeat}(' ', 2)), 2) AS plan,
		          LEFT(CONCAT(COALESCE(adj.period_code, ' '), {$repeat}(' ', 2)), 2) AS service_months,
		          LEFT(CONCAT(COALESCE(adj.adjustment_code, ' '), ' '), 1) AS filler1,
		          CONCAT(
		             COALESCE((CASE WHEN COALESCE(adj.salary, 0) < 0 THEN '-' ELSE ' ' END), ' '),
		             RIGHT(CONCAT({$repeat}('0', 10), COALESCE(CAST(ROUND(ABS(adj.salary)*100, 0) AS VARCHAR(20)), '0')), 10)
		          ) AS salary,
		          CONCAT(
		             COALESCE((CASE WHEN COALESCE(adj.deductions, 0) < 0 THEN '-' ELSE ' ' END), ' '),
		             RIGHT(CONCAT({$repeat}('0', 10), COALESCE(CAST(ROUND(ABS(adj.deductions)*100, 0) AS VARCHAR(20)), '0')), 10)
		          ) AS retirement_contribution_employee,
		          CONCAT(
		             COALESCE((CASE WHEN COALESCE(adj.deductions, 0) < 0 THEN '-' ELSE ' ' END), ' '),
		             RIGHT(CONCAT({$repeat}('0', 10), COALESCE(CAST(ROUND(ABS(adj.contributions)*100, 0) AS VARCHAR(20)), '0')), 10)
		          ) AS retirement_contribution_employer,
		          CONCAT(
		             COALESCE((CASE WHEN COALESCE(adj.leave_hours, 0) < 0 THEN '-' ELSE ' ' END), ' '),
		             RIGHT(CONCAT({$repeat}('0', 5), COALESCE(CAST(ROUND(ABS(COALESCE(adj.leave_hours, 0)) * 100, 0) AS VARCHAR(5)), '0')), 5)
		          ) AS annual_hours,
		          RIGHT(CONCAT({$repeat}('0', 12), COALESCE(fac.code, '0')), 12)  AS dept,
		          RIGHT(CONCAT(' ', COALESCE(FieldOptionCode(u.gender), ' ')), 1) AS gender,
		          LEFT(CONCAT(COALESCE({$birthDateMDY}, '0'), {$repeat}('0', 8)), 8) AS birth_date,
		          COALESCE(j.eeo, ' ') AS eeo,
		          CASE WHEN COALESCE(j.job_id, 999999) = 297165 THEN '     0000063000' ELSE {$repeat}(' ', 15) END AS filler3,
		          LEFT(CONCAT(COALESCE(ea.address, ' '), {$repeat}(' ', 129)), 129) AS address,
		          COALESCE({$hireDateMDY}, {$repeat}('0', 8)) AS continuous_employment_date,
		          COALESCE({$termDateMDY}, {$repeat}(' ', 8)) AS termination_date,
		          CONCAT(
		             ' ',
		             COALESCE(
		                RIGHT(
		                   CONCAT(
		                      {$repeat}('0', 10),
		                      CAST(ROUND((COALESCE(j.contract_annualized_earnings, 0) + COALESCE(ans.annualized, 0)) * 100) AS VARCHAR(20))
		                   ),
		                   10
		                ),
		                {$repeat}('0', 10)
		             )
		          ) AS contract_annualized_earnings,
		          CAST(' 0000000000' AS TEXT) AS col_2,
		          CAST(' 0000000000' AS TEXT) AS col_3,
		          CAST(' ' AS TEXT) AS filler_4,
		          CAST(' 0000000000' AS TEXT) AS col_4,
		          CAST(' 0000000000' AS TEXT) AS col_5,
		          CAST(' 0000000000' AS TEXT) AS col_6,
		          CAST(' 0000000000' AS TEXT) AS col_7,
		          CAST(' ' AS TEXT) AS filler_5,
		          CAST(' 0000000000' AS TEXT) AS col_8,
		          CAST(' 0000000000' AS TEXT) AS col_9,
		          CAST(' 0000000000' AS TEXT) AS col_10,
		          CAST(' 0000000000' AS TEXT) AS col_11,
		          RIGHT(CONCAT(' ', COALESCE(u.exempt_from_public_record, ' ')), 1) AS public_records_exempt,
		          CAST(' ' AS TEXT) AS filler_6,
		          LEFT(CONCAT(COALESCE(ee.email, ' '), {$repeat}(' ', 60)), 60) AS email,
		          CAST({$repeat}(' ', 15) AS TEXT) as col_12
		FROM      users u
		JOIN      gl_pr_retirement_adjustments adj
		          ON  adj.staff_id = u.staff_id
		          AND adj.deleted IS NULL
		          AND adj.send_month = {$startDateM}
		          AND adj.send_year = {$startDateY}
		JOIN      gl_pr_retirement_plans adj_plan ON adj.plan_id = adj_plan.id
		LEFT JOIN most_recent_job j ON j.staff_id = u.staff_id AND rownum = 1
		LEFT JOIN gl_facilities fac ON fac.id = j.primary_facility_id
		LEFT JOIN employee_email ee ON ee.staff_id = u.staff_id AND ee.rownum = 1
		LEFT JOIN employee_addr ea ON ea.staff_id = u.staff_id AND ea.rownum = 1
		LEFT JOIN annual_supps ans ON ans.staff_id = u.staff_id",
	"sumrows" => "
		SELECT   CAST('54007' AS TEXT) AS prefix,
		         CAST('9999999TR' AS TEXT) AS ssn,
		         {$startDateMY} AS mmyear,
		         RIGHT(
		            CONCAT(
		               {$repeat}('0', 6),
		               COALESCE(CAST((SELECT COUNT(*) FROM (SELECT * FROM frsrows UNION SELECT * FROM adjrows) a) AS text), '0')
		            ),
		            6
		         ) AS name,
		         CAST(NULL AS TEXT) AS plan,
		         CAST(NULL AS TEXT) AS service_months,
		         CAST(NULL AS TEXT) AS filler1,
		         CAST(NULL AS TEXT) AS salary,
		         CAST(NULL AS TEXT) AS retirement_contribution_employee,
		         CAST(NULL AS TEXT) AS retirement_contribution_employeer,
		         CAST(NULL AS TEXT) AS annual_hours,
		         CAST(NULL AS TEXT) AS dept,
		         CAST(NULL AS TEXT) AS gender,
		         CAST(NULL AS TEXT) AS birth_date,
		         CAST(NULL AS TEXT) AS eeo,
		         CAST(NULL AS TEXT) AS filler3,
		         CAST(NULL AS TEXT) AS address,
		         CAST(NULL AS TEXT) AS continuous_employment_date,
		         CAST(NULL AS TEXT) AS termination_date,
		         CAST(NULL AS TEXT) AS contract_annualized_earnings,
		         CAST(NULL AS TEXT) AS col_2,
		         CAST(NULL AS TEXT) AS col_3,
		         CAST(NULL AS TEXT) AS filler_4,
		         CAST(NULL AS TEXT) AS col_4,
		         CAST(NULL AS TEXT) AS col_5,
		         CAST(NULL AS TEXT) AS col_6,
		         CAST(NULL AS TEXT) AS col_7,
		         CAST(NULL AS TEXT) AS filler_5,
		         CAST(NULL AS TEXT) AS col_8,
		         CAST(NULL AS TEXT) AS col_9,
		         CAST(NULL AS TEXT) AS col_10,
		         CAST(NULL AS TEXT) AS col_11,
		         CAST(NULL AS TEXT) AS public_records_exempt,
		         CAST(NULL AS TEXT) AS filler_6,
		         CAST(NULL AS TEXT) AS email,
		         CAST(NULL AS TEXT) AS col_12"
];

$tmp = [];

foreach ($sql as $k => $v) {
	$tmp[] = "{$k} AS ({$v})";
}

$tmp = implode(", ", $tmp);
$report = DBEscapeString("WITH {$tmp} SELECT * FROM frsrows UNION SELECT * FROM adjrows UNION SELECT * FROM sumrows ORDER BY ssn");

$res = Database::get("SELECT id FROM custom_reports_folders WHERE title = 'Payroll Monthly/Quarterly' AND package = 'Finance' AND parent_id = -1");

if (empty($res)) {
	$nxt = Database::nextSql("custom_reports_folders_seq");
	Database::query("INSERT INTO custom_reports_folders (id, parent_id, title, package) VALUES ({$nxt}, -1, 'Payroll Monthly/Quarterly', 'Finance')");
	$res = Database::get("SELECT id FROM custom_reports_folders WHERE title = 'Payroll Monthly/Quarterly' AND package = 'Finance' AND parent_id = -1");
}

$folderID = $res[0]["ID"];

$res = Database::get("SELECT cr.id FROM custom_reports cr WHERE cr.parent_id = {$folderID} AND cr.title = 'FRS Integration Report'");

if (empty($res)) {
	$nxt = Database::nextSql("custom_reports_seq");
	Database::query("
		INSERT
		INTO   custom_reports (id, title, profile_ids, multiple_queries, package, parent_id)
		VALUES ({$nxt}, 'FRS Integration Report', '||1||', 'N', 'Finance', {$folderID})"
	);
	$res = Database::get("SELECT cr.id FROM custom_reports cr WHERE cr.parent_id = {$folderID} AND cr.title = 'FRS Integration Report'");
}

$reportID = $res[0]["ID"];

Database::query("UPDATE custom_reports SET query = '{$report}' WHERE id = {$reportID}");

$res = Database::get("SELECT 'x' FROM custom_reports_variables WHERE variable_name = '{DCLASS}'");

if (empty($res)) {
	$nxt = Database::nextSql("custom_reports_variables_seq");
	Database::query("
		INSERT
		INTO   custom_reports_variables (id, variable_name, variable_type, default_value, interface_title, package)
		VALUES ({$nxt}, '{DCLASS}', 0, '010', 'Deduction Class', 'Finance')"
	);
}

return true;
