<?php
if(empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$date_type = Database::$type === 'mssql' ? 'DATETIME2' : 'TIMESTAMP';
$text_type =  Database::$type === 'mssql'? 'VARCHAR(MAX)' : 'TEXT';

if(!Database::tableExists('gl_bus_driver_pools')){
	Database::query('create table gl_bus_driver_pools (
	id bigint PRIMARY KEY,
	name text
)');
}

if(!Database::tableExists('gl_bus_drivers')){
	Database::query('create table gl_bus_drivers (
	id bigint PRIMARY KEY,
	staff_id numeric,
	priority numeric,
	pool_id numeric
)');
	Database::query("CREATE INDEX gl_db_staff_id_idx ON gl_bus_drivers (staff_id)");
}

if(!Database::tableExists('gl_field_trip_facility')){
	Database::query('create table gl_field_trip_facility (
		id bigint PRIMARY KEY,
		name '.$text_type.',
		code '.$text_type.',
		address '.$text_type.',
		address_line_2 '.$text_type.',
		city '.$text_type.',
		zipcode '.$text_type.',
		state '.$text_type.',
		phone '.$text_type.',
		email '.$text_type.'
	)');
}

if(!Database::tableExists('gl_field_trip_location')){
	Database::query('create table gl_field_trip_location(
	id bigint PRIMARY KEY,
	field_trip_request_id numeric,
	pickup_date '.$date_type.',
	dropoff_date '.$date_type.',

	pickup_location '.$text_type.',
	dropoff_location '.$text_type.',

	pickup_location_code numeric,
	dropoff_location_code numeric,
	description '.$text_type.'
)');
	Database::query("CREATE INDEX gl_ftl_field_trip_request_id_idx ON gl_field_trip_location (field_trip_request_id)");
}

if(!Database::tableExists('gl_field_trip_location_bus_driver')){
	Database::query('create table gl_field_trip_location_bus_driver (
	id bigint PRIMARY KEY,
	field_trip_location_id numeric,
	bus_driver_id numeric,
	assigned_date '.$date_type.',
	status varchar(1)
)');
	Database::query("CREATE INDEX gl_ftlbd_field_trip_location_id_idx ON gl_field_trip_location_bus_driver (field_trip_location_id)");
}

if(!Database::tableExists('gl_field_trip_request')) {
	Database::query('create table gl_field_trip_request(
	id bigint PRIMARY KEY,
	internal bigint,
	recurring numeric,
	start_date '.$date_type.',
	end_date '.$date_type.',

	created_date '.$date_type.',
	request_date '.$date_type.',

	requesting_facility numeric,
	fiscal_year numeric,
	dest_code varchar(4),
	type '.$text_type.',

	name '.$text_type.',
	description '.$text_type.',
	creator numeric,
	status varchar(4),


	number_of_students numeric,
	number_of_adults numeric,
	number_of_wheelchairs numeric,
	number_of_wheelchair_aides numeric,
	number_of_buses numeric,

	contact numeric,

	phone '.$text_type.',
	email '.$text_type.',

	days '.$text_type.',

	completed numeric default 0

	)');
}

if(!Database::columnExists('gl_journal_detail', 'FIELD_TRIP_REQUEST_ID')) {
	Database::createColumn('gl_journal_detail', 'FIELD_TRIP_REQUEST_ID', 'numeric');
}

if(!Database::tableExists('gl_ap_field_trip_request_allocation')){
	Database::query('create table gl_ap_field_trip_request_allocation (
	id bigint PRIMARY KEY,
	accounting_strip_hash  varchar(255),
	accounting_strip_id bigint,
	amount numeric,
	deleted bigint,
	journal_date '.$date_type.',
	line_item_id bigint,
	reference_number integer,
	field_trip_request_id bigint,
	transaction_date '.$date_type.'
)');
}

if(!Database::tableExists('gl_field_trip_collection')) {
	Database::query('create table gl_field_trip_collection(
	id bigint PRIMARY KEY,
	date '.$date_type.',
	final_number_of_students numeric,
	final_number_of_adults numeric,
	final_number_of_wheelchairs numeric,
	final_number_of_wheelchair_aides numeric,
	final_number_of_buses numeric,

	total_mileage numeric,
	final_cost numeric,
	completed numeric default 0
	)');
}

if(!Database::tableExists('gl_field_trip_location_to_collection')) {
	Database::query('create table gl_field_trip_location_to_collection(
	id bigint PRIMARY KEY,
	field_trip_location_id numeric,
	field_trip_collection_id numeric
	)');

	Database::query("CREATE INDEX gl_ftltc_field_trip_location_id_idx ON gl_field_trip_location_to_collection (field_trip_location_id)");
	Database::query("CREATE INDEX gl_ftltc_field_trip_collection_id_idx ON gl_field_trip_location_to_collection (field_trip_collection_id)");
}

?>