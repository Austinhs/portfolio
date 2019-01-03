<?php
if(empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$date_type = Database::$type === 'mssql' ? 'DATETIME2' : 'TIMESTAMP';
$text_type =  Database::$type === 'mssql'? 'VARCHAR(MAX)' : 'TEXT';


if(!Database::tableExists('gl_position_change_request')) {
	Database::query('create table gl_position_change_request(
		id bigint PRIMARY KEY,

		created_date '.$date_type.',
		effective_date '.$date_type.',

		facility numeric,
		fiscal_year numeric,

		name '.$text_type.',
		description '.$text_type.',
		requester_id numeric,
		status varchar(4),

		reason '.$text_type.'
	)');
}

if(!Database::columnExists('gl_journal_detail', 'POSITION_CHANGE_REQUEST_ID')) {
	Database::createColumn('gl_journal_detail', 'POSITION_CHANGE_REQUEST_ID', 'numeric');
}

if(!Database::tableExists('gl_hr_position_change_request_line_items')) {
	Database::query('create table gl_hr_position_change_request_line_items (
		id bigint PRIMARY KEY,
		position_change_request_id bigint,

		transaction_date '.$date_type.',

		from_position bigint,
		to_position bigint,
		hours numeric,
		employee_id bigint,
		job_title_id bigint,
		salary_slot bigint,
		salary numeric,
		move_allocation integer,
		courses_taught '.$text_type.',

		deleted bigint
	)');
}

if(!Database::tableExists('gl_hr_position_change_request_allocation')) {
	Database::query('create table gl_hr_position_change_request_allocation (
		id bigint PRIMARY KEY,
		line_item_id bigint,
		position_change_request_id bigint,

		accounting_strip_hash  varchar(255),
		accounting_strip_id bigint,

		"percent" numeric,
		transaction_date '.$date_type.',

		deleted bigint
	)');
}

if (!Database::columnExists("gl_pr_staff_job_positions", "group_errors"))
	Database::createColumn("gl_pr_staff_job_positions", "group_errors", "text");
