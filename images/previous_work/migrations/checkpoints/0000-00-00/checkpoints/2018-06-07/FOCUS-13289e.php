<?php
//svn merge svn://focus-sis.com/focus/branches/8.0/dev/FOCUS-13289e -r212320:HEAD
Migrations::depend("FOCUS-14076");
Migrations::depend("FOCUS-14874");

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

define('UserClass','FocusUser');

			// $log = KLogger2::instance(dirname(__FILE__), KLogger2::DEBUG);
			// $log->logInfo("migration 13289e");

Database::begin();

//Data for fields
$demo_fields = [
	[
		'title' => 'Leave Accrual Date',
		'column' => 'leave_accrual_date',
		'type' => 'date'
	]
];

$access_profiles = Database::get("SELECT PROFILE_ID FROM PERMISSION WHERE \"key\" = 'hr::demographic'");

$sort_increment = 0;
foreach ($demo_fields as $field) {

	if (empty(FocusUser::getFieldByAlias($field['column']))) {

		$sort_increment = 0;
		$erp_cat = CustomFieldCategory::getOneAndLoad("title = 'Employee Demographic'");
		$erp_cat_id = $erp_cat->getId();

		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');

		//if (!empty($field['new'])) {
			$cf->setNewRecord(1);
		//}
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();
		$field_column = $cf->getColumnName();

		Permissions::checkAndInsert($access_profiles, [
			"FocusUser:{$field_id}:can_view",
			"FocusUser:{$field_id}:can_edit"
		]);

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($erp_cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(2);
		$cfjc->persist();

		// $col_name_array = ERPUser::getFieldByAlias('leave_accrual_date');
		// $col_name = $col_name_array["column_name"];

		// Database::query("
		// 	update users
		// 	set {$col_name} =
		// 	(
		// 		select min(sj.hire_date)
		// 		from gl_pr_staff_jobs sj
		// 		join gl_pr_staff_job_positions sjp on sjp.staff_id = sj.staff_id
		// 		and sjp.fyear = ".PRGeneral::getContractYear()."
		// 		where sj.staff_id = users.staff_id
		// 	)
		// 	where {$col_name} is not null
		// ");
	}

}

Database::commit();
