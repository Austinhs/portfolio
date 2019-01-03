<?php


if(!Database::tableExists('relationship_fields')) {

	$create_table = "create table relationship_fields (
		id bigint primary key,
		state_code varchar(255) null,
		title varchar(255)
	);";

	Database::query($create_table);
	if(!Database::sequenceExists('relationship_fields_seq')) {
		Database::createSequence('relationship_fields_seq');
	}

	$records = [
		['state_code' => '', 'title' => 'None'],
		['state_code' => 'Father', 'title' => 'Father'],
		['state_code' => 'Mother', 'title' => 'Mother'],
		['state_code' => 'Stepfather', 'title' => 'Stepfather'],
		['state_code' => 'Stepmother', 'title' => 'Stepmother'],
		['state_code' => 'Parent', 'title' => 'Parent'],
		['state_code' => 'Stepparent', 'title' => 'Stepparent'],
		['state_code' => 'Grandfather', 'title' => 'Grandfather'],
		['state_code' => 'Grandmother', 'title' => 'Grandmother'],
		['state_code' => 'Aunt', 'title' => 'Aunt'],
		['state_code' => 'Uncle', 'title' => 'Uncle'],
		['state_code' => 'Guardian', 'title' => 'Guardian'],
		['state_code' => 'Surrogate', 'title' => 'Surrogate'],
		['state_code' => 'Emergency', 'title' => 'Emergency']
	];

	Database::insert('relationship_fields', 'relationship_fields_seq', ['state_code', 'title'], $records);

}


if(!Database::tableExists('address_field_flags')) {

	$create_table = "create table address_field_flags (
		id bigint primary key,
		title varchar(255),
		address varchar(1),
		contact_details varchar(1),
		contact varchar(1),
		phone_numbers varchar(1),
		database_column_name varchar(255)
	);";

	Database::query($create_table);

	if(!Database::sequenceExists('address_field_flags_seq')) {
		Database::createSequence('address_field_flags_seq');
	}

	if(!Database::columnExists('address', 'unlisted')) {
		if(Database::columnExists('address', 'phone_unlisted')) {
			Database::renameColumn('phone_unlisted', 'unlisted', 'address');
		} else {
			Database::createColumn('address', 'unlisted', 'varchar', 1);
		}
	}

	if(!Database::columnExists('address', 'callout')) {
		if(Database::columnExists('address', 'phone_callout')) {
			Database::renameColumn('phone_callout', 'callout', 'address');
		} else {
			Database::createColumn('address', 'callout', 'varchar', 1);
		}
	}

	if(!Database::columnExists('address', 'blocked')) {
		if(Database::columnExists('address', 'phone_blocked')) {
			Database::renameColumn('phone_blocked', 'blocked', 'address');
		} else {
			Database::createColumn('address', 'blocked', 'varchar', 1);
		}
	}

	$columns = [
		'title',
		'address',
		'contact_details',
		'phone_numbers',
		'database_column_name'
	];

	$records = [
		[
			'title'                => 'Unlisted',
			'address'              => '1',
			'contact_details'      => '1',
			'phone_numbers'        => '1',
			'database_column_name' => 'unlisted'
		],
		[
			'title'                => 'Callout',
			'address'              => '1',
			'contact_details'      => '1',
			'phone_numbers'        => '1',
			'database_column_name' => 'callout'
		],
		[
			'title'                => 'Blocked',
			'address'              => '1',
			'contact_details'      => '1',
			'phone_numbers'        => '1',
			'database_column_name' => 'blocked'
		],
	];

	Database::insert('address_field_flags', 'address_field_flags_seq', $columns, $records);

	$columns = [
		'title',
		'contact',
		'database_column_name'
	];

	$records = [
		[
			'title'                => 'Custody',
			'contact'              => '1',
			'database_column_name' => 'custody'
		],
		[
			'title'                => 'Emergency',
			'contact'              => '1',
			'database_column_name' => 'emergency'
		],
		[
			'title'                => 'Pick Up',
			'contact'              => '1',
			'database_column_name' => 'pick_up'
		],
	];

	Database::insert('address_field_flags', 'address_field_flags_seq', $columns, $records);

}