<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"])
	return false;

Database::begin();

$fields = [
	"period_pay"  => "numeric",
	"daily_rate"  => "numeric",
	"work_days"   => "bigint",
	"pay_type_id" => "bigint",
	"start_date"  => "date"
];

foreach (["gl_pr_run_control_new_hires", "gl_pr_run_control_new_hire_supplements"]  as $table)
	foreach ($fields as $k => $v)
		if (!Database::columnExists($table, $k))
			Database::createColumn($table, $k, $v);

Database::query("
	UPDATE gl_pr_run_control_new_hires
	SET    period_pay = COALESCE(w.contract_pay_period, 0.00)
	FROM   gl_pr_current_fyear_job_wages w, gl_pr_run_controls rc
	WHERE  rc.id = gl_pr_run_control_new_hires.run_id
	AND    rc.fyear = w.fyear
	AND    w.staff_job_id = gl_pr_run_control_new_hires.staff_job_id
	AND    w.deleted IS NULL
	AND    w.supplement = 'N'
	AND    w.status = 'A'
	AND    gl_pr_run_control_new_hires.period_pay IS NULL"
);

Database::query("
	UPDATE gl_pr_run_control_new_hires
	SET    daily_rate = COALESCE(w.contract_daily_pay, 0.00)
	FROM   gl_pr_current_fyear_job_wages w, gl_pr_run_controls rc
	WHERE  rc.id = gl_pr_run_control_new_hires.run_id
	AND    rc.fyear = w.fyear
	AND    w.staff_job_id = gl_pr_run_control_new_hires.staff_job_id
	AND    w.deleted IS NULL
	AND    w.supplement = 'N'
	AND    w.status = 'A'
	AND    gl_pr_run_control_new_hires.daily_rate IS NULL"
);

Database::query("
	UPDATE gl_pr_run_control_new_hires
	SET    pay_type_id = w.pay_type_id
	FROM   gl_pr_current_fyear_job_wages w, gl_pr_run_controls rc
	WHERE  rc.id = gl_pr_run_control_new_hires.run_id
	AND    rc.fyear = w.fyear
	AND    w.staff_job_id = gl_pr_run_control_new_hires.staff_job_id
	AND    w.deleted IS NULL
	AND    w.supplement = 'N'
	AND    w.status = 'A'
	AND    gl_pr_run_control_new_hires.pay_type_id IS NULL"
);

Database::query("
	UPDATE gl_pr_run_control_new_hires
	SET    start_date = w.date_start
	FROM   gl_pr_current_fyear_job_wages w, gl_pr_run_controls rc
	WHERE  rc.id = gl_pr_run_control_new_hires.run_id
	AND    rc.fyear = w.fyear
	AND    w.staff_job_id = gl_pr_run_control_new_hires.staff_job_id
	AND    w.deleted IS NULL
	AND    w.supplement = 'N'
	AND    w.status = 'A'
	AND    gl_pr_run_control_new_hires.start_date IS NULL"
);

$res = Database::get("
	SELECT rcpt.date_pay_period_beg, w.date_end, w.fyear, w.pay_type_id, nh.date_from, nh.id
	FROM   gl_pr_run_control_new_hires nh
	JOIN   gl_pr_run_controls rc ON rc.id = nh.run_id
	JOIN   gl_pr_current_fyear_job_wages w ON w.fyear = rc.fyear AND w.staff_job_id = nh.staff_job_id
	JOIN   gl_pr_run_control_pay_types rcpt ON rcpt.run_control_id = nh.run_id AND rcpt.pay_type_id = w.pay_type_id
	WHERE  w.deleted IS NULL
	AND    w.supplement = 'N'
	AND    w.status = 'A'
	AND    nh.work_days IS NULL
	AND    rcpt.date_pay_period_beg IS NOT NULL"
);

foreach ($res as $r)
{
	$fsd   = date("Y-m-d", strtotime(PRGeneral::getContractStartingDate($r["FYEAR"])));
	$fed   = date("Y-m-d", strtotime(PRGeneral::getContractEndingDate($r["FYEAR"])));

	if (!$r["DATE_FROM"])
		$r["DATE_FROM"] = $fsd;

	if (!$r["DATE_END"])
		$r["DATE_END"] = $fed;

	$eddt  = PRGeneral::addDays(min($r["DATE_PAY_PERIOD_BEG"], $r["DATE_END"]), -1);
	$wkdys = PRGeneral::getDaysWorked($r["FYEAR"], $r["PAY_TYPE_ID"], $r["DATE_FROM"], $eddt);

	Database::query("UPDATE gl_pr_run_control_new_hires SET work_days = {$wkdys} WHERE id = {$r["ID"]}");
}

Database::query("
	UPDATE gl_pr_run_control_new_hire_supplements
	SET    period_pay = sjs.period_pay
	FROM   gl_pr_staff_job_supplements sjs
	WHERE  gl_pr_run_control_new_hire_supplements.staff_supplement_id = sjs.id
	AND    gl_pr_run_control_new_hire_supplements.period_pay IS NULL
	AND    sjs.period_pay IS NOT NULL"
);

Database::query("
	UPDATE gl_pr_run_control_new_hire_supplements
	SET    start_date = w.date_start
	FROM   gl_pr_current_fyear_job_wages w, gl_pr_run_controls rc
	WHERE  rc.id = gl_pr_run_control_new_hire_supplements.run_id
	AND    rc.fyear = w.fyear
	AND    w.staff_job_id = gl_pr_run_control_new_hire_supplements.staff_job_id
	AND    w.deleted IS NULL
	AND    w.supplement = 'N'
	AND    w.status = 'A'
	AND    gl_pr_run_control_new_hire_supplements.start_date IS NULL"
);

$res = Database::get("
	SELECT rcpt.date_pay_period_beg, w.date_end, w.fyear, w.pay_type_id, nhs.date_from, nhs.staff_supplement_id,
	       COALESCE(sjs.total_pay, 0) AS total_pay, nhs.id
	FROM   gl_pr_run_control_new_hire_supplements nhs
	JOIN   gl_pr_run_controls rc ON rc.id = nhs.run_id
	JOIN   gl_pr_current_fyear_job_wages w ON w.fyear = rc.fyear AND w.staff_job_id = nhs.staff_job_id
	JOIN   gl_pr_run_control_pay_types rcpt ON rcpt.run_control_id = nhs.run_id AND rcpt.pay_type_id = w.pay_type_id
	JOIN   gl_pr_staff_job_supplements sjs ON sjs.id = nhs.staff_supplement_id
	WHERE  w.deleted IS NULL
	AND    w.supplement = 'N'
	AND    w.status = 'A'
	AND    (nhs.work_days IS NULL OR nhs.daily_rate IS NULL OR nhs.pay_type_id IS NULL)
	AND    rcpt.date_pay_period_beg IS NOT NULL"
);

foreach ($res as $r)
{
	$fsd    = date("Y-m-d", strtotime(PRGeneral::getContractStartingDate($r["FYEAR"])));
	$fed    = date("Y-m-d", strtotime(PRGeneral::getContractEndingDate($r["FYEAR"])));
	$tWkdys = Database::get("
		SELECT COUNT(*) AS work_days
		FROM   gl_pr_staff_job_supplements sjs
		JOIN   gl_pr_pay_types_for_year pty ON pty.fyear = sjs.fyear
		JOIN   gl_pr_calendar_work_days cwd ON cwd.calendar_id = pty.calendar_id
		WHERE  sjs.id = {$r["STAFF_SUPPLEMENT_ID"]}
		AND    pty.pay_type_id = {$r["PAY_TYPE_ID"]}
		AND    cwd.days_remaining IS NOT NULL
		AND    cwd.work_date BETWEEN COALESCE(sjs.beg_date, '{$fsd}') AND COALESCE(sjs.end_date, '{$fed}')
		AND    sjs.deleted IS NULL"
	);

	Database::query("
		UPDATE gl_pr_run_control_new_hire_supplements
		SET    work_days = {$tWkdys[0]["WORK_DAYS"]}
		WHERE  id = {$r["ID"]}
		AND    work_days IS NULL"
	);
	Database::query("
		UPDATE gl_pr_run_control_new_hire_supplements
		SET    daily_rate = ROUND({$r["TOTAL_PAY"]} / work_days, 2)
		WHERE  COALESCE(work_days, 0) != 0
		AND    id = {$r["ID"]}
		AND    daily_rate IS NULL"
	);
	Database::query("
		UPDATE gl_pr_run_control_new_hire_supplements
		SET    pay_type_id = {$r["PAY_TYPE_ID"]}
		WHERE  id = {$r["ID"]}
		AND    pay_type_id IS NULL"
	);
}

Database::commit();

return true;
