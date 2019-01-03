<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

//Depends on FOCUS-10778 which was created VIA MetaData
if(!Database::tableExists('gl_pr_run_control_timesheets')) {
	return false;
}

//This migration creates
//author_id: id given to record when created to filter the records and show only the ones the user com_create_guid
//package: for the other tables so we know its coming from school/dep
//This is the same affect for time,overtime,leave, and misc batchs

if(Database::tableExists('gl_pr_run_control_staff_misc_compensation_batches') 
	&& Database::tableExists('gl_pr_run_control_timesheets')
	&& Database::tableExists('gl_pr_run_control_manual_overtime')
	&& Database::tableExists('gl_pr_staff_leave_requests')) {
		
		if (!Database::columnExists('gl_pr_run_control_staff_misc_compensation_batches', 'author_id')) {
			Database::createColumn('gl_pr_run_control_staff_misc_compensation_batches', 'author_id', 'bigint');
		}
		
		if (!Database::columnExists('gl_pr_run_control_staff_misc_compensation_batches', 'author_id')) {
			Database::createColumn('gl_pr_run_control_staff_misc_compensation_batches', 'author_id', 'bigint');
		}
		
		if (!Database::columnExists('gl_pr_run_control_timesheets', 'author_id')) {
			Database::createColumn('gl_pr_run_control_timesheets', 'author_id', 'bigint');
		}
		
		if (!Database::columnExists('gl_pr_run_control_timesheets', 'package')) {
			Database::createColumn('gl_pr_run_control_timesheets', 'package', 'varchar', 255);
		}
		
		if (!Database::columnExists('gl_pr_run_control_manual_overtime', 'author_id')) {
			Database::createColumn('gl_pr_run_control_manual_overtime', 'author_id', 'bigint');
		}
		
		if (!Database::columnExists('gl_pr_run_control_manual_overtime', 'package')) {
			Database::createColumn('gl_pr_run_control_manual_overtime', 'package', 'varchar', 255);
		}
		
		if (!Database::columnExists('gl_pr_staff_leave_requests', 'author_id')) {
			Database::createColumn('gl_pr_staff_leave_requests', 'author_id', 'bigint');
		}
		
		if (!Database::columnExists('gl_pr_staff_leave_requests', 'package')) {
			Database::createColumn('gl_pr_staff_leave_requests', 'package', 'varchar', 255);
		}
} else {
	return false;
}
