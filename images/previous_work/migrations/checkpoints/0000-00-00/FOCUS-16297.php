<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$timestamp = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";
$now       = (Database::$type === "mssql") ? "CURRENT_TIMESTAMP" : "NOW()";

if (!Database::tableExists("gl_wo_request_type")) {
	$sql =
		"CREATE TABLE gl_wo_request_type (
			id BIGINT PRIMARY KEY,
			deleted INT,
			name VARCHAR(255),
			profile_ids TEXT,
			accounting_strip_id BIGINT,
			active INT,
			allow_fixed_assets INT,
			allow_products INT,
			bypass_pick_list INT,
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wo_issue_type")) {
	$sql =
		"CREATE TABLE gl_wo_issue_type (
			id BIGINT PRIMARY KEY,
			deleted INT,
			name VARCHAR(255),
			profile_ids TEXT,
			active INT,
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wo_priority")) {
	$sql =
		"CREATE TABLE gl_wo_priority (
			id BIGINT PRIMARY KEY,
			deleted INT,
			code VARCHAR(255),
			title VARCHAR(255),
			description TEXT,
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wo_route")) {
	$sql =
		"CREATE TABLE gl_wo_route (
			id BIGINT PRIMARY KEY,
			deleted INT,
			name VARCHAR(255),
			facility_ids TEXT,
			request_type_ids TEXT,
			assignee_id BIGINT,
			hourly_rate NUMERIC(28,10),
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wo_product")) {
	$sql =
		"CREATE TABLE gl_wo_product (
			id BIGINT PRIMARY KEY,
			deleted INT,
			name VARCHAR(255),
			description TEXT,
			cost NUMERIC(28,10),
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wo_product_to_wh_item")) {
	$sql =
		"CREATE TABLE gl_wo_product_to_wh_item (
			id BIGINT PRIMARY KEY,
			deleted INT,
			product_id BIGINT,
			warehouse_item_id BIGINT,
			warehouse_item_quantity NUMERIC(28,10),
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wo_request")) {
	$sql =
		"CREATE TABLE gl_wo_request (
			id BIGINT PRIMARY KEY,
			deleted INT,
			name VARCHAR(255),
			request_type_id BIGINT,
			number BIGINT,
			priority_id BIGINT,
			requester_id BIGINT,
			email_address VARCHAR(255),
			phone_number VARCHAR(255),
			time_available TEXT,
			facility_id BIGINT,
			building_id BIGINT,
			room_id BIGINT,
			area VARCHAR(255),
			issue_type_id BIGINT,
			issue_description TEXT,
			fixed_asset_id BIGINT,
			product_id BIGINT,
			repair_actions TEXT,
			created_at {$timestamp},
			request_date {$timestamp},
			approved_date {$timestamp},
			completed_date {$timestamp},
			route_id BIGINT,
			assignee_id BIGINT,
			estimated_start_date {$timestamp},
			estimated_end_date {$timestamp},
			estimated_hours NUMERIC(28,10),
			request_status CHAR,
			completion_status CHAR,
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wo_request_replacement")) {
	$sql =
		"CREATE TABLE gl_wo_request_replacement (
			id BIGINT PRIMARY KEY,
			deleted INT,
			request_id BIGINT,
			warehouse_item_id BIGINT,
			warehouse_item_quantity NUMERIC(28,10),
			warehouse_item_unit_price NUMERIC(28, 10),
			tools TEXT,
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wo_request_cost")) {
	$sql =
		"CREATE TABLE gl_wo_request_cost (
			id BIGINT PRIMARY KEY,
			deleted INT,
			request_id BIGINT,
			description TEXT,
			quantity NUMERIC(28,10),
			unit_price NUMERIC(28, 10),
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::tableExists("gl_wh_picklist_work_order")) {
	$sql =
		"CREATE TABLE gl_wh_picklist_work_order (
			id BIGINT PRIMARY KEY,
			deleted INT,
			picklist_id BIGINT,
			request_id BIGINT,
			completed INT,
			updated_at {$timestamp}
		)";

	Database::query($sql);
}

if (!Database::columnExists("gl_fa_building", "updated_at")) {
	Database::createColumn("gl_fa_building", "updated_at", $timestamp);

	$sql =
		"UPDATE
			gl_fa_building
		SET
			updated_at = {$now}";

	Database::query($sql);
}

if (!Database::columnExists("gl_fa_room", "updated_at")) {
	Database::createColumn("gl_fa_room", "updated_at", $timestamp);

	$sql =
		"UPDATE
			gl_fa_room
		SET
			updated_at = {$now}";

	Database::query($sql);
}

if (!Database::columnExists("gl_ap_approval_record", "updated_at")) {
	Database::createColumn("gl_ap_approval_record", "updated_at", $timestamp);

	$sql =
		"UPDATE
			gl_ap_approval_record
		SET
			updated_at = {$now}";

	Database::query($sql);
}

if (!Database::columnExists("gl_wh_picklists", "type")) {
	Database::createColumn("gl_wh_picklists", "type", "VARCHAR", 16);

	$sql =
		"UPDATE
			gl_wh_picklists
		SET
			type = 'wh'";

	Database::query($sql);
}

if (!Database::columnExists("gl_wh_picklists", "created_by")) {
	Database::createColumn("gl_wh_picklists", "created_by", "BIGINT");
}

Database::commit();
return true;
?>