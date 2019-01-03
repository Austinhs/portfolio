<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_adjustment_codes', 'leave_type')) {
	Database::createColumn('gl_pr_adjustment_codes', 'leave_type', 'char', '1');

	//Query old data and convert it to Type E for earned
	$oldDataQuery = Database::get(
		"SELECT ID
		FROM gl_pr_adjustment_codes
		WHERE leave_more_or_less != 'N'
		");

	$oldData = [];

	if($oldDataQuery) {
		foreach($oldDataQuery as $record) {
			$oldData[] = $record['ID'];
		}

		$oldData = implode(',', $oldData);

		Database::query(
			"UPDATE gl_pr_adjustment_codes
			SET leave_type = 'E'
			WHERE id IN ($oldData)
		");
	}

	if(Database::columnExists('gl_pr_adjustment_codes', 'leave_more_or_less')) {
		Database::dropColumn('gl_pr_adjustment_codes', 'leave_more_or_less');
	}

	// if(!Database::columnExists('users', 'leave_accrual_date')) {
	// 	Database::createColumn('users', 'leave_accrual_date', 'timestamp');

		// Database::query("
		// 	update users
		// 	set leave_accrual_date =
		// 	(
		// 		select continuous_employment_date
		// 		from gl_hr_demographic dg
		// 		where dg.staff_id = users.staff_id
		// 		and dg.deleted is null
		// 	limit 1
		// 	)
		// ");

	//}
}

if(!Database::columnExists('gl_pr_history_run_leave_adjustments', 'leave_type')) {
	Database::createColumn('gl_pr_history_run_leave_adjustments', 'leave_type', 'char(1)');
}

if(!Database::columnExists('gl_pr_staff_leave_earned', 'run_id')) {
	Database::createColumn('gl_pr_staff_leave_earned', 'run_id', 'int');
}

// if(!Database::columnExists('gl_hr_demographic', 'leave_experience_date')) {
// 	Database::createColumn('gl_hr_demographic', 'leave_experience_date', 'timestamp');

// 	// Database::query("
// 	// 	update gl_hr_demographic
// 	// 	set leave_experience_date = continuous_employment_date
// 	// ");

// 	Database::query("
// 		insert into gl_meta_field
// 		(title,display_type,meta_table_id,name,id,system,type)
// 		values
// 		(
// 		'Leave Experience Date',
// 		'text',
// 		'13711583096125',
// 		'leave_experience_date',
// 		15106030520311,
// 		1,'TIMESTAMP'
// 		)
// 	");

// 	Database::query("
// 		insert into gl_meta_field_category
// 		(meta_category_id,meta_field_id,id,system,sort)
// 		values
// 		(
// 		14226484844372,
// 		15106030520311,
// 		15106037605818,
// 		1,
// 		1
// 		)
// 	");

// }

if(!Database::columnExists('gl_pr_staff_leave_earned', 'overridden')) {
	Database::createColumn('gl_pr_staff_leave_earned', 'overridden', 'int');
}

if(!Database::columnExists('gl_pr_staff_leave_earned', 'overridden_by')) {
	Database::createColumn('gl_pr_staff_leave_earned', 'overridden_by', 'bigint');
}

if(!Database::columnExists('gl_pr_staff_leave_earned', 'change_by')) {
	Database::createColumn('gl_pr_staff_leave_earned', 'change_by', 'bigint');
}


