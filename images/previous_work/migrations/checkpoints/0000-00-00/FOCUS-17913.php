<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}
//svn merge svn://focus-sis.com/focus/branches/8.0/dev/FOCUS-17913 -r280072:HEAD

Database::begin();

if(!Database::indexExists("gl_pr_history_run_deductions", "gl_pr_history_run_deductions_yymm"))
	Database::query("create index gl_pr_history_run_deductions_yymm on gl_pr_history_run_deductions (yymm)");

$calendar_YY = date("Y");
Database::query("
	delete from gl_pr_history_cyear_deductions where cyear = {$calendar_YY}
");

$yy = date("y");
$yy01 = $yy."01";
$yy12 = $yy."12";


Database::query("

	insert into gl_pr_history_cyear_deductions
	(id, cyear ,staff_id,staff_deduction_id,deduction_id,deduction_contribution, deduction_employee, amount_earned)
	select ".db_seq_nextval('GL_PR_SEQ').",{$calendar_YY},hd.staff_id,hd.staff_deduction_id,hd.deduction_id,cont.deduction_contribution,ded.deduction_employee,wages.wages

		from gl_pr_history_run_deductions hd
		join (
			SELECT rw.staff_id,rw.deduction_id,SUM(rw.deduction_contribution) AS deduction_contribution
			FROM gl_pr_history_run_deductions rw
			WHERE rw.yymm between '{$yy01}' and '{$yy12}'
			AND rw.deduction_contribution IS NOT NULL
			and rw.status = 'F'
			group by rw.staff_id,rw.deduction_id
		) as cont on cont.staff_id = hd.staff_id and cont.deduction_id = hd.deduction_id

		join (
			SELECT rw.staff_id,rw.deduction_id,SUM(rw.deduction_employee) AS deduction_employee
			FROM gl_pr_history_run_deductions rw
			WHERE rw.yymm between '{$yy01}' and '{$yy12}'
			AND rw.deduction_employee IS NOT NULL
			and rw.status = 'F'
			group by rw.staff_id,rw.deduction_id
		) as ded on ded.staff_id = hd.staff_id and ded.deduction_id = hd.deduction_id

		join (
			SELECT rw.staff_id,rw.deduction_id,SUM(rw.wages) AS wages
			FROM gl_pr_history_run_deductions rw
			WHERE rw.yymm between '{$yy01}' and '{$yy12}'
			and rw.wages is not null
			and rw.status = 'F'
			group by rw.staff_id,rw.deduction_id
		) as wages on wages.staff_id = hd.staff_id and wages.deduction_id = hd.deduction_id

	where hd.yymm between '{$yy01}' and '{$yy12}'
	group by hd.staff_id,hd.staff_deduction_id,hd.deduction_id,cont.deduction_contribution,ded.deduction_employee,wages.wages
");














Database::commit();
return true;
