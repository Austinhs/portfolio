<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

	if(Database::tableExists('gl_hr_ess_info_chg_requests')) {
		//drop request table cause its no longer needed
		Database::query('DROP TABLE gl_hr_ess_info_chg_requests');
		//drop batch table to recreate fields/index values -- but only if requests table still exists otherwise this migration has been ran already.
		Database::query('DROP TABLE gl_hr_ess_info_chg_batch');
	}

	//Create new tables for the form types if they do not exist already
	if(!Database::tableExists('gl_hr_ess_info_chg_batch')) {
		Database::query(
			"CREATE TABLE gl_hr_ess_info_chg_batch (
				id BIGINT PRIMARY KEY NOT NULL,
				deleted BIGINT,
				type VARCHAR(255),
				request_staff_id BIGINT,
				author_staff_id BIGINT,
				status CHAR(1)
			)
		");
		
		Database::columnExists('gl_hr_ess_info_chg_batch', 'date_requested')      ?: Database::createColumn('gl_hr_ess_info_chg_batch', 'date_requested', 'TIMESTAMP');
		Database::columnExists('gl_hr_ess_info_chg_batch', 'approved_date')      ?: Database::createColumn('gl_hr_ess_info_chg_batch', 'approved_date', 'TIMESTAMP');
	}
	
	if (!Database::tableExists('gl_hr_ess_info_chg_name')) {
		Database::query(
			"CREATE TABLE gl_hr_ess_info_chg_name (
				id BIGINT PRIMARY KEY NOT NULL,
				first_name VARCHAR(50),
				last_name VARCHAR(50),
				batch_id BIGINT
			)
		");
	}

	if(!Database::tableExists('gl_hr_ess_approval_history')) {
		Database::query(
			"CREATE TABLE gl_hr_ess_approval_history (
				id BIGINT PRIMARY KEY NOT NULL,
				record_id BIGINT,
				tier VARCHAR(255),
				decision CHAR(1),
				decision_by BIGINT
			)
		");

		Database::columnExists('gl_hr_ess_approval_history', 'decision_date') ?: Database::createColumn('gl_hr_ess_approval_history', 'decision_date', 'TIMESTAMP');
	}

	if (!Database::tableExists('gl_hr_ess_info_chg_address')) {
		Database::query(
			"CREATE TABLE gl_hr_ess_info_chg_address (
				id BIGINT PRIMARY KEY NOT NULL,
				address VARCHAR(255),
				additional VARCHAR(255),
				city VARCHAR(50),
				state VARCHAR(50),
				zip VARCHAR(10),
				primary_address CHAR(1),
				payment CHAR(1),
				mailing CHAR(1),
				batch_id BIGINT
			)
		");
	}

	if (!Database::tableExists('gl_hr_ess_info_chg_contact')) {
		Database::query(
			"CREATE TABLE gl_hr_ess_info_chg_contact (
				id BIGINT PRIMARY KEY NOT NULL,
				contact_type_id BIGINT,
				contact_info VARCHAR(255),
				batch_id BIGINT
			)
		");
	}

	if (!Database::tableExists('gl_hr_ess_info_chg_emergency')) {
		Database::query(
			"CREATE TABLE gl_hr_ess_info_chg_emergency (
				id BIGINT PRIMARY KEY NOT NULL,
				contact_first_name VARCHAR(50),
				contact_last_name VARCHAR(50),
				relationship VARCHAR(25),
				phone_contact_option_one VARCHAR(20),
				phone_contact_option_two VARCHAR(20),
				phone_option_one VARCHAR(20),
				phone_option_two VARCHAR(20),
				batch_id BIGINT
			)
		");
	}

	if (!Database::tableExists('gl_hr_ess_info_chg_deposits')) {
		Database::query(
			"CREATE TABLE gl_hr_ess_info_chg_deposits (
				id BIGINT PRIMARY KEY NOT NULL,
				account_type CHAR(1),
				routing_number VARCHAR(255),
				account_number VARCHAR(255),
				amount BIGINT,
				status CHAR(1),
				catchall CHAR(1),
				batch_id BIGINT
			)
		");
	}

	if (!Database::tableExists('gl_hr_ess_info_chg_w4')) {
		Database::query(
			"CREATE TABLE gl_hr_ess_info_chg_w4 (
				id BIGINT PRIMARY KEY NOT NULL,
				w4_allowances BIGINT,
				w4_status CHAR(1),
				w4_additional VARCHAR(255),
				w4_opt_out_paper_w2 CHAR(1),
				batch_id BIGINT
			)
		");
	}
Database::commit();

function dropIndex($table, $index) {
	Database::query(
		"DROP INDEX {$index} ON {$table}
	");
}
