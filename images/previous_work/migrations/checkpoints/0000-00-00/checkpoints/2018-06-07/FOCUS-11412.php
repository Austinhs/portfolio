<?php

$dropNotNullFromTimestamp = function($table_name, $column_name) {
	if (Database::$type === 'mssql') {
		$is_nullable = Database::get("
			SELECT is_nullable 
			FROM information_schema.columns 
			WHERE table_name = '{$table_name}' AND column_name = '{$column_name}'
		");

		if ($is_nullable[0]['IS_NULLABLE'] == 'NO') {
			Database::query("ALTER TABLE {$table_name} DROP COLUMN {$column_name}");
			Database::query("ALTER TABLE {$table_name} ADD {$column_name} TIMESTAMP NULL");
		}
	}
	else if (Database::$type === 'postgres') {
		Database::query("ALTER TABLE {$table_name} ALTER COLUMN {$column_name} DROP NOT NULL");
	}
};

$nullableColumns = [
	'lesson_course' =>
	[
		'course_num'        => 'varchar(16)'
		,'course_period_id' => 'numeric'
	],

	'lesson_files' =>
	[
		'course_period_id' => 'numeric'
		,'hash'            => 'varchar(64)'
		,'lesson'          => 'numeric'
		,'original_name'   => 'varchar(1024)'
		,'path'            => 'varchar(1024)'
		,'size'            => 'numeric'
		,'staff_id'        => 'bigint'
		,'upload_time'     => 'datetime'
	],

	'lesson_layout' =>
	[
		'component_data'      => 'varchar(max)'
		,'course_id'          => 'bigint'
		,'course_period_id'   => 'bigint'
		,'data'               => 'varchar(max)'
		,'duration'           => 'bigint'
		,'end_date'           => 'datetime'
		,'end_date_enabled'   => 'char(1)'
		,'format'             => 'varchar(16)'
		,'last_updated'       => 'datetime'
		,'lesson_plan_id'     => 'numeric'
		,'locked'             => 'char(1)'
		,'marking_period_id'  => 'bigint'
		,'notes'              => 'varchar(1024)'
		,'plan_import_id'     => 'bigint'
		,'reviewed'           => 'char(1)'
		,'saved'              => 'char(1)'
		,'sequence'           => 'numeric'
		,'staff_id'           => 'bigint'
		,'standards'          => 'varchar(512)'
		,'start_date'         => 'datetime'
		,'start_date_enabled' => 'char(1)'
		,'syear'              => 'numeric'
		,'template'           => 'char(1)'
		,'title'              => 'varchar(255)'
		,'unit_import_id'     => 'bigint'
		,'visibility'         => 'char(1)'
	],

	'lesson_plan' =>
	[
		'category_id'          => 'numeric'
		,'components'          => 'varchar(128)'
		,'import_id'           => 'bigint'
		,'is_curriculum_guide' => 'varchar(1)'
		,'permissions'         => 'varchar(max)'
		,'syear'               => 'numeric'
		,'tags'                => 'varchar(max)'
		,'template'            => 'char(1)'
		,'title'               => 'varchar(255)'
		,'web_page_id'         => 'numeric'
	],

	'lesson_plan_category' =>
	[
		'name' => 'varchar(200)'
	],

	'lesson_plan_component' =>
	[
		'alt_titles'     => 'varchar(max)'
		,'default_value' => 'varchar(max)'
		,'description'   => 'varchar(max)'
		,'disabled'      => 'char(1)'
		,'options'       => 'varchar(max)'
		,'public'        => 'varchar(1)'
		,'restriction'   => 'char(1)'
		,'sort_order'    => 'numeric'
		,'student_view'  => 'char(1)'
		,'title'         => 'varchar(32)'
	],

	'lesson_plan_component_template' =>
	[
		'components' => 'varchar(128)'
		,'title'     => 'varchar(128)'
	],

	'lesson_plan_sharing' =>
	[
		'access'          => 'bigint'
		,'date_created'   => 'datetime'
		,'lesson_plan_id' => 'bigint'
		,'shared_to'      => 'bigint'
		,'shared_type'    => 'varchar(16)'
		,'source_user'    => 'bigint'
	],

	'lesson_plan_sharing_group' =>
	[
		'code'       => 'varchar(16)'
		,'name'      => 'varchar(64)'
		,'school_id' => 'bigint'
	],

	'lesson_plan_sharing_group_member' =>
	[
		'group_id' => 'bigint'
		,'user_id' => 'bigint'
	],

	'forum' =>
	[
		'assigned_date' => 'datetime'
		,'assignments'  => 'varchar(255)'
		,'course_id'    => 'numeric'
		,'intro'        => 'varchar(max)'
		,'lesson_id'    => 'numeric'
		,'sections'     => 'varchar(255)'
		,'settings'     => 'varchar(max)'
		,'time_created' => 'bigint'
		,'title'        => 'varchar(500)'
	],

	'forum_post' =>
	[
		'approval_time'     => 'bigint'
		,'approved'         => 'char(1)'
		,'content'          => 'varchar(max)'
		,'course_period_id' => 'numeric'
		,'grade'            => 'varchar(30)'
		,'last_edit_time'   => 'bigint'
		,'reply_to'         => 'numeric'
		,'sequence'         => 'numeric'
		,'time_posted'      => 'bigint'
	],

	'forum_thread' =>
	[
		'author'            => 'numeric'
		,'course_period_id' => 'bigint'
		,'forum_id'         => 'numeric'
		,'hidden'           => 'char(1)'
		,'is_open'          => 'varchar(1)'
		,'last_edit_time'   => 'bigint'
		,'open'             => 'char(1)'
		,'time_created'     => 'bigint'
		,'title'            => 'varchar(500)'
		,'views'            => 'numeric'
	],

	'forum_thread_view' =>
	[
		'last_viewed' => 'bigint'
	],

	'forum_user' =>
	[
		'post_count' => 'numeric'
	],

	'instruction_group' =>
	[
		'description'     => 'varchar(255)'
		,'end_time'       => 'varchar(20)'
		,'learning_group' => 'varchar(255)'
		,'rotation'       => 'varchar(255)'
		,'standard'       => 'bigint'
		,'start_time'     => 'varchar(20)'
	]
];

if (!Database::columnExists('forum', 'due_date')) {
	Database::createColumn('forum', 'due_date', 'timestamp', null,true);
}

if (!Database::columnExists('forum_thread', 'is_open')) {
	Database::createColumn('forum_thread', 'is_open', null,'varchar(1)');
}

$dropNotNullFromTimestamp('lesson_plan', 'last_updated');
$dropNotNullFromTimestamp('forum', 'due_date');

foreach ($nullableColumns as $table => $columns) {
	foreach ($columns as $column => $type) {
		if (Database::$type === 'mssql') {
			Database::query("ALTER TABLE {$table} ALTER COLUMN \"{$column}\" {$type} NULL");
		}
		else if (Database::$type === 'postgres') {
			Database::query("ALTER TABLE {$table} ALTER COLUMN \"{$column}\" DROP NOT NULL");
		}
	}
}
