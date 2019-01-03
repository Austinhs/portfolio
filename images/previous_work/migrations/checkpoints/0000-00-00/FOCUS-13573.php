<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::tableExists('gl_hr_clock_ins')) {
		Database::query(
		"CREATE TABLE gl_hr_clock_ins (
			id BIGINT PRIMARY KEY NOT NULL,
			travel_from_facility BIGINT,
			travel_from_facility_other VARCHAR(255),
			travel_to_facility BIGINT,
			travel_to_facility_other VARCHAR(255),
			travel_notes TEXT,
			ip_clock_in VARCHAR(255),
			ip_clock_out VARCHAR(255),
			notes text,
			status CHAR(2),
			facility_id BIGINT,
			facility_id_other VARCHAR(255),
			time_schedule_id BIGINT,
			position_id BIGINT,
			staff_id BIGINT,
			approver BIGINT,
			yymmdd CHAR(6),
			fyear BIGINT,
			week_index BIGINT,
			origin VARCHAR(255),
			special_request_id BIGINT,
			force_request CHAR(1),
			posted BIGINT
		)");

		Database::createColumn('gl_hr_clock_ins', 'approval_date', 'timestamp');
		Database::createColumn('gl_hr_clock_ins', 'approved_clock_in', 'timestamp');
		Database::createColumn('gl_hr_clock_ins', 'approved_clock_out', 'timestamp');
		Database::createColumn('gl_hr_clock_ins', 'original_clock_in', 'timestamp');
		Database::createColumn('gl_hr_clock_ins', 'original_clock_out', 'timestamp');
		Database::createColumn('gl_hr_clock_ins', 'requested_clock_in', 'timestamp');
		Database::createColumn('gl_hr_clock_ins', 'requested_clock_out', 'timestamp');
}

if(Database::tableExists('gl_hr_clock_ins')) {
	if(!Database::columnExists('gl_hr_clock_ins', 'deny_reason')) {
		Database::createColumn('gl_hr_clock_ins', 'deny_reason', 'text');
	}

	if(!Database::columnExists('gl_hr_clock_ins', 'grace_period_clock_in')) {
		Database::createColumn('gl_hr_clock_ins', 'grace_period_clock_in', 'timestamp');
	}

	if(!Database::columnExists('gl_hr_clock_ins', 'grace_period_clock_out')) {
		Database::createColumn('gl_hr_clock_ins', 'grace_period_clock_out', 'timestamp');
	}

	if(!Database::columnExists('gl_hr_clock_ins', 'approved_clock_in_type')) {
		Database::createColumn('gl_hr_clock_ins', 'approved_clock_in_type', 'varchar', '255');
	}

	if(!Database::columnExists('gl_hr_clock_ins', 'approved_clock_out_type')) {
		Database::createColumn('gl_hr_clock_ins', 'approved_clock_out_type', 'varchar', '255');
	}
}

if(!Database::tableExists('gl_hr_time_schedules')) {
	Database::query(
		"CREATE TABLE gl_hr_time_schedules (
			id BIGINT PRIMARY KEY NOT NULL,
			title VARCHAR(255),
			code VARCHAR(255),
			start_time VARCHAR(255),
			end_time VARCHAR(255),
			break_length_minutes BIGINT,
			automatic_breaks BIGINT,
			variable_schedule BIGINT
		)
	");
}

if(!Database::tableExists('gl_hr_time_schedule_special_requests')) {
	Database::query(
		"CREATE TABLE gl_hr_time_schedule_special_requests (
			id BIGINT PRIMARY KEY NOT NULL,
			title VARCHAR(255),
			code VARCHAR(255),
			deleted bigint,
			active bigint
		)
	");
}

if(!Database::columnExists('gl_pr_positions', 'time_schedule_id')) {
	Database::createColumn('gl_pr_positions', 'time_schedule_id', 'BIGINT');
}

if(!Database::columnExists('gl_pr_staff_job_positions', 'time_schedule_id')) {
	Database::createColumn('gl_pr_staff_job_positions', 'time_schedule_id', 'BIGINT');
}

if(!Database::columnExists('gl_pr_positions', 'timecard_automatic_dockage')) {
	Database::createColumn('gl_pr_positions', 'timecard_automatic_dockage', 'BIGINT');
}

if(!Database::columnExists('gl_pr_staff_job_positions', 'timecard_automatic_dockage')) {
	Database::createColumn('gl_pr_staff_job_positions', 'timecard_automatic_dockage', 'BIGINT');
}

if(!Database::columnExists('gl_pr_positions', 'special_request_schedule_ids')) {
	Database::createColumn('gl_pr_positions', 'special_request_schedule_ids', 'text');
}

if(!Database::columnExists('gl_pr_staff_job_positions', 'special_request_schedule_ids')) {
	Database::createColumn('gl_pr_staff_job_positions', 'special_request_schedule_ids', 'text');
}

if(!Database::tableExists('gl_hr_time_schedule_ip')) {
	Database::query(
		"CREATE TABLE gl_hr_time_schedule_ip (
			id BIGINT PRIMARY KEY NOT NULL,
			title VARCHAR(255),
			octect_1 varchar(20),
			octect_2 varchar(20),
			octect_3 varchar(20),
			octect_4 varchar(20),
			octect_5 varchar(20),
			octect_6 varchar(20),
			octect_7 varchar(20),
			octect_8 varchar(20),
			type varchar(255)
		)
	");
}

if(!Settings::get('ta_max_hours')) {
	Settings::set('ta_max_hours', 40);
	Settings::set('ta_allow_current_week', true);
	Settings::set('ta_original_clock_yes_no', true);
	Settings::set('ta_default_original', true);
	Settings::set('ta_ip_format', 'ipv4');
}

if(!Database::tableExists('gl_hr_special_request_masking')) {
	Database::query(
		"CREATE TABLE gl_hr_special_request_masking (
			ID BIGINT PRIMARY KEY NOT NULL,
			mask_array TEXT,
			special_request_id BIGINT
		)
	");
}
