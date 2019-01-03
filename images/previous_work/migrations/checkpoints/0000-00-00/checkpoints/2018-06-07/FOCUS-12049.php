<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql = "INSERT INTO PERMISSION (\"key\",profile_id)
	SELECT 'ap::allow_non_po_invoices' as \"key\",p.profile_id
	FROM permission p
	WHERE
		\"key\" = 'ap::allow_non_po_invoices_district'
	AND NOT EXISTS (select '' from permission p2 where p2.profile_id=p.profile_id and p2.\"key\"='ap::allow_non_po_invoices')";

Database::query($sql);

if(!Database::columnExists('gl_pr_staff_job_incentive_pay', 'fyear')) {
	Database::query("alter table gl_pr_staff_job_incentive_pay add fyear numeric");
}

Database::query("update gl_pr_staff_job_incentive_pay set fyear='2016'");

Database::query("update gl_pr_staff_job_incentive_pay set fyear='2017' where exists (select '' from gl_pr_slot_pay sp,gl_pr_slots s where s.id=sp.slot_id and sp.id=gl_pr_staff_job_incentive_pay.slot_pay_id and s.fyear='2017')");

if(Database::columnExists('gl_accounting_strip','category_program') && Database::$type!=='mssql')
{
	Database::query("update gl_budget b set accounting_strip_id=(select parent_id from gl_accounting_strip s where s.id=b.accounting_strip_id) where year='2017' and exists (select '' from gl_accounting_strip s where s.id=b.accounting_strip_id and s.category_program is not null) and not exists (select '' from gl_budget where year='2017' and accounting_strip_id=(select parent_id from gl_accounting_strip s where s.id=b.accounting_strip_id))");
	Database::query("delete FROM gl_budget b WHERE year='2017' AND EXISTS (SELECT '' FROM gl_accounting_strip s WHERE s.id=b.accounting_strip_id AND s.category_program IS NOT NULL)");

	Database::query("delete from gl_budget b where exists (select '' from gl_budget b2 where b2.accounting_strip_id=b.accounting_strip_id and b2.id<b.id and b2.year=b.year and b2.deleted is null) and deleted is null");
}
?>
