<?php

if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

function createObjects($table, $columns, $required = [], $skip_adding_seq = false) {
	if (!Database::tableExists($table)) {
		Database::query("CREATE TABLE {$table} (tmp int)");
	}

	foreach ($columns as $column => $type) {
		if (!Database::columnExists($table, $column)) {
			$null = $column === 'id' ? false : !in_array($column, $required);

			Database::createColumn($table, $column, $type, '', $null);

			if ($column === 'id' && !$skip_adding_seq) {
				$sequence_name = "{$table}_{$column}_seq";

				if (!Database::sequenceExists($sequence_name)) {
					Database::createSequence($sequence_name);
				}

				if (Database::getPrimaryKey($table) === null) {
					Database::query("ALTER TABLE {$table} ADD PRIMARY KEY ({$column})");
				}
			}
		}
	}

	if (Database::columnExists($table, 'tmp')) {
		Database::query("ALTER TABLE {$table} DROP COLUMN tmp");
	}
}

Database::begin();

if (!Database::columnExists('master_courses', 'store_category_id')) {
	Database::createColumn('master_courses', 'store_category_id', 'bigint');
}

if (!Database::columnExists('course_periods', 'store_category_id')) {
	Database::createColumn('course_periods', 'store_category_id', 'bigint');
}

if (!Database::columnExists('course_periods', 'store_internal')) {
	Database::createColumn('course_periods', 'store_internal', 'varchar(1)');
}

if (!Database::columnExists('gl_pos_cash_drawer', 'portal_enabled')) {
	Database::createColumn('gl_pos_cash_drawer', 'portal_enabled', 'smallint');
}

/**
 * Create store_category table
 */
createObjects(
	'store_category',
	[
		'id'           => 'bigint',
		'parent_id'    => 'bigint',
		'name'         => 'varchar',
		'district'     => 'smallint',
		'for_parents'  => 'smallint',
		'for_students' => 'smallint'
	],
	[
		'id',
		'name'
	]
);

/**
 * Create store_category_school table
 */
createObjects(
	'store_category_school',
	[
		'id'                => 'bigint',
		'store_category_id' => 'bigint',
		'school_id'         => 'bigint'
	],
	[
		'id',
		'store_category_id',
		'school_id'
	]
);

/**
 * Create store_item table
 */
createObjects(
	'store_item',
	[
		'id'        => 'bigint',
		'school_id' => 'bigint',
		'name'      => 'varchar',
		'price'     => 'decimal(9,2)',
		'deleted'   => 'smallint'
	],
	[
		'id',
		'name'
	]
);

/**
 * Create store_item_component table
 */
createObjects(
	'store_item_component',
	[
		'id'            => 'bigint',
		'store_item_id' => 'bigint',
		'source_class'  => 'varchar',
		'source_id'     => 'bigint',
		'name'          => 'varchar',
		'price'         => 'decimal(9,2)',
		'quantity'      => 'smallint',
		'start_date'    => 'date',
		'end_date'      => 'date',
		'deleted'       => 'smallint'
	],
	[
		'id',
		'store_item_id',
		'quantity'
	]
);

/**
 * Create store_category_item table
 */
createObjects(
	'store_category_item',
	[
		'id'                => 'bigint',
		'store_category_id' => 'bigint',
		'store_item_id'     => 'bigint'
	],
	[
		'id',
		'store_category_id',
		'store_item_id'
	]
);

/**
 * Create store_cart table
 */
createObjects(
	'store_cart',
	[
		'id'                 => 'bigint',
		'owner_profile'      => 'varchar',
		'owner_class'        => 'varchar',
		'owner_id'           => 'bigint',
		'name'               => 'varchar',
		'saved'              => 'smallint',
		'completed'          => 'smallint',
		'transaction_number' => 'bigint',
		'created_at'         => 'timestamp',
		'updated_at'         => 'timestamp'
	],
	[
		'id',
		'owner_profile',
		'owner_class',
		'owner_id',
		'created_at'
	]
);

/**
 * Create store_cart_item table
 */
createObjects(
	'store_cart_item',
	[
		'id'            => 'bigint',
		'store_cart_id' => 'bigint',
		'store_item_id' => 'bigint',
		'student_id'    => 'bigint',
		'quantity'      => 'smallint',
		'deleted'       => 'smallint'
	],
	[
		'id',
		'store_cart_id',
		'store_item_id',
		'quantity'
	]
);

createObjects(
	'store_log',
	[
		'id'                 => 'bigint',
		'created_by'         => 'bigint',
		'transaction_number' => 'bigint',
		'level'              => 'int',
		'message'            => 'text',
		'created_at'         => 'timestamp'
	],
	[
		'id',
		'created_by',
		'level',
		'message',
		'created_at'
	]
);

createObjects(
	'store_student_schedule_log',
	[
		'id'                 => 'bigint',
		'store_cart_item_id' => 'bigint',
		'store_cart_id'      => 'bigint',
		'store_item_id'      => 'bigint',
		'transaction_number' => 'bigint',
		'student_id'         => 'bigint',
		'schedule_id'        => 'bigint',
		'created_at'         => 'timestamp',
	],
	[
		'id',
		'store_cart_item_id',
		'store_cart_id',
		'store_item_id',
		'transaction_number',
		'student_id',
		'schedule_id',
		'created_at',
	]
);

Database::commit();
