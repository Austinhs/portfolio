<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_run_control_pay_schedules', 'pay_schedule_rollover_id')) {
	Database::createColumn('gl_pr_run_control_pay_schedules', 'pay_schedule_rollover_id', 'bigint');
}

if(!Database::columnExists('gl_pr_staff_job_positions', 'rollover_from_id')) {
	Database::createColumn('gl_pr_staff_job_positions', 'rollover_from_id', 'bigint');
}

if(!Database::columnExists('gl_hr_staff_experience', 'experience_type_id')) {
	Database::createColumn('gl_hr_staff_experience', 'experience_type_id', 'bigint');

	//Go through each of the existing staff experience records and create a link to the experience type id to fill in the new column
	$types = ExperienceType::getAllAndLoad();
	foreach($types as $type) {
		Database::query(
			"UPDATE gl_hr_staff_experience
			SET experience_type_id = {$type->getId()}
			WHERE experience_type = '{$type->getCode()}'
		");
	}
}

if(!Database::columnExists('gl_hr_experience_types', 'linked_pay_type_ids')) {
	Database::createColumn('gl_hr_experience_types', 'linked_pay_type_ids', 'text');
}

if(!Database::columnExists('gl_pr_staff_experience_rollover', 'type_id')) {
	Database::createColumn('gl_pr_staff_experience_rollover', 'type_id', 'bigint');
}
