<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::tableExists('gl_hr_employee_leave_change')) {
	Database::query(
		"CREATE TABLE gl_hr_employee_leave_change (
			id BIGINT PRIMARY KEY,
			type varchar(255),
			from_staff_id bigint,
			to_staff_id bigint,
			bucket_id bigint,
			leave_request_id_from bigint,
			leave_earned_id_to bigint,
			amount double precision
	)");

	Database::createColumn('gl_hr_employee_leave_change', 'date', 'TIMESTAMP');
}

if(!Database::tableExists('gl_hr_leave_transfer_credited')) {
	Database::query(
		"CREATE TABLE gl_hr_leave_transfer_credited (
			id BIGINT PRIMARY KEY,
			staff_id bigint,
			fyear bigint,
			bucket_id bigint,
			leave_earned_id bigint,
			amount double precision
	)");

	Database::createColumn('gl_hr_leave_transfer_credited', 'date', 'TIMESTAMP');
}

if(!Database::columnExists('gl_pr_leave_buckets', 'leave_bank')) {
	Database::createColumn('gl_pr_leave_buckets', 'leave_bank', 'CHAR');
}

if(!Database::tableExists('gl_hr_leave_banks')) {
	Database::query(
		"CREATE TABLE gl_hr_leave_banks (
			id BIGINT PRIMARY KEY,
			title VARCHAR(255),
			bucket_id BIGINT,
			deduct_bucket_id BIGINT,
			allowed_pay_types TEXT,
			deleted BIGINT
		)
	");
}

if(!Database::tableExists('gl_hr_leave_bank_enlistments')) {
	Database::query(
		"CREATE TABLE gl_hr_leave_bank_enlistments (
			id BIGINT PRIMARY KEY,
			staff_id BIGINT,
			bank_id BIGINT
		)");

	Database::createColumn('gl_hr_leave_bank_enlistments', 'date_start', 'TIMESTAMP');
	Database::createColumn('gl_hr_leave_bank_enlistments', 'date_end', 'TIMESTAMP');
}

if(!Database::tableExists('gl_hr_leave_bank_transactions')) {
	Database::query(
		"CREATE TABLE gl_hr_leave_bank_transactions (
			id BIGINT PRIMARY KEY,
			staff_id BIGINT,
			bank_id BIGINT,
			type varchar(255),
			recovered CHAR(1),
			leave_request_id BIGINT,
			leave_earned_id BIGINT,
			amount double precision
	)");

	Database::createColumn('gl_hr_leave_bank_transactions', 'transaction_date', 'TIMESTAMP');
}
