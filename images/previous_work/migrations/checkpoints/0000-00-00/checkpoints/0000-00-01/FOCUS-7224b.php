<?php

// Depend on the 8.0.1 migration
Migrations::depend('FOCUS-7224a');

// Depend on all migrations that were added in 8.0.1
Migrations::depend('FOCUS-5069');
Migrations::depend('FOCUS-5466');
Migrations::depend('FOCUS-5467');
Migrations::depend('FOCUS-5468');
Migrations::depend('FOCUS-5469');
Migrations::depend('FOCUS-5470');
Migrations::depend('FOCUS-5481');
Migrations::depend('FOCUS-5496');
Migrations::depend('FOCUS-5501');
Migrations::depend('FOCUS-5516');
Migrations::depend('FOCUS-5581');
Migrations::depend('FOCUS-5619');
Migrations::depend('FOCUS-5641');
Migrations::depend('FOCUS-5715');
Migrations::depend('FOCUS-5728');
Migrations::depend('FOCUS-5809');
Migrations::depend('FOCUS-5891');
Migrations::depend('FOCUS-5903');
Migrations::depend('FOCUS-5996');
Migrations::depend('FOCUS-6197');

echo "8.2.1 Migration";

if(Database::$type === 'mssql') {
	// Primary keys to add
	$create_pkeys = [
		// This fails on some MSSQL sites
		// 'login_history' => "
		// 	ALTER TABLE [dbo].[LOGIN_HISTORY] ADD PRIMARY KEY CLUSTERED
		// 	(
		// 		[ID] ASC
		// 	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		// ",

		'courses' => "
			alter table courses add primary key clustered (
			course_id asc
			)
		"
	];

	// Indexes to drop
	$drop_indexes = [
		'schedule' => [
			'IX_SchID_SYr_ID' => "
				DROP INDEX [IX_SchID_SYr_ID] ON [dbo].[schedule] WITH ( ONLINE = OFF )
			"
		]
	];

	// Indexes to create
	$create_indexes = [
		'users' => [
			'ix_deleted' => "
					create index ix_deleted on users(
				deleted
				) where deleted is not null
			"
		],

		'permission' => [
			'ix_key' => "
				create index ix_key on [permission](
				[key]
				)
			"
		],

		'co_teacher_days' => [
			'ix_crsperid_tchid' => "
				create index ix_crsperid_tchid on co_teacher_days(
				course_period_id
				, teacher_id
				)
			"
		],

		'custom_field_select_options' => [
			'ix_srcclass_srcid' => "
				create index ix_srcclass_srcid on custom_field_select_options(
				source_class
				, source_id
				)
			",

			'ix_srcclass_cd_inact_del' => "
				create index ix_srcclass_cd_inact_del on custom_field_select_options(
				source_class
				, code
				, inactive
				, deleted
				)
			"
		],

		'schedule_requests' => [
			'ix_syr_schid' => "
				create index ix_syr_schid on schedule_requests(
				syear
				, school_id
				) include(student_id)
			"
		],

		'master_courses' => [
			'ix_syr_crsid' => "
				create index ix_syr_crsid on master_courses(
				syear
				, course_id
				)
			"
		],

		'schedule' => [
			'ix_schid_syr_id' => "
				create nonclustered index ix_schid_syr_id on schedule(
				syear
				, school_id
				, id
				)
			"
		],

		'login_history' => [
			'ix_dt_type_objid_failed' => "
				CREATE NONCLUSTERED INDEX [ix_dt_type_objid_failed] ON [dbo].[LOGIN_HISTORY]
				(
					[DATE] ASC,
					[TYPE] ASC,
					[OBJECT_ID] ASC,
					[FAILED] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			",

			'IX_ObjID_Failed_Date_Type_ID' => "
				CREATE NONCLUSTERED INDEX [IX_ObjID_Failed_Date_Type_ID] ON [dbo].[LOGIN_HISTORY]
				(
					[OBJECT_ID] ASC,
					[FAILED] ASC,
					[DATE] ASC,
					[TYPE] ASC,
					[ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			",

			'IX_username' => "
				CREATE NONCLUSTERED INDEX [IX_username] ON [dbo].[LOGIN_HISTORY]
				(
					[username] ASC
				)
				INCLUDE ( 	[DATE],
					[FAILED]) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			",

			'ix_ipaddr' => "
				create index ix_ipaddr on login_history(
				ip_address
				)
			"
		],

		'courses' => [
			'IX_CrsID_SYr_SchID' => "
				CREATE NONCLUSTERED INDEX [IX_CrsID_SYr_SchID] ON [dbo].[courses]
				(
					[course_id] ASC,
					[syear] ASC,
					[school_id] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			",

			'IX_schID_crsID' => "
				CREATE NONCLUSTERED INDEX [IX_schID_crsID] ON [dbo].[courses]
				(
					[school_id] ASC,
					[course_id] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			",

			'IX_SchID_SYr_CrsID' => "
				CREATE NONCLUSTERED INDEX [IX_SchID_SYr_CrsID] ON [dbo].[courses]
				(
					[school_id] ASC,
					[syear] ASC,
					[course_id] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			",

			'IX_SubjID' => "
				CREATE NONCLUSTERED INDEX [IX_SubjID] ON [dbo].[courses]
				(
					[subject_id] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			"
		]
	];

	// Create some primary keys if they don't already exist
	foreach($create_pkeys as $table => $create_sql) {
		$existing = Database::getPrimaryKey($table);

		if(empty($existing)) {
			Database::query($create_sql);
		}
	}

	// Drop some indexes if they do already exist
	foreach($drop_indexes as $table => $tmp_indexes) {
		$existing = Database::getIndexes($table);
		$existing = array_change_key_case($existing, CASE_LOWER);

		foreach($tmp_indexes as $index_name => $drop_sql) {
			$tmp_index_name = strtolower($index_name);

			if(isset($existing[$tmp_index_name])) {
				Database::query($drop_sql);
			}
		}
	}

	// Create some indexes if they don't already exist
	foreach($create_indexes as $table => $tmp_indexes) {
		$existing = Database::getIndexes($table);
		$existing = array_change_key_case($existing, CASE_LOWER);

		foreach($tmp_indexes as $index_name => $create_sql) {
			$tmp_index_name = strtolower($index_name);

			if(!isset($existing[$tmp_index_name])) {
				Database::query($create_sql);
			}
		}
	}
}

// "text" gets mapped to VARCHAR(MAX) for mssql
Database::changeColumnType('database_object_log', 'before', 'text');
Database::changeColumnType('database_object_log', 'after', 'text');
Database::changeColumnType('database_object_log', 'query', 'text');

Database::changeColumnType('referral_codes', 'actions', 'text');
Database::changeColumnType('referral_codes', 'code', 'varchar', 20);
Database::changeColumnType('referral_codes', 'profiles', 'text');
Database::changeColumnType('referral_codes', 'title', 'varchar', 255);
Database::changeColumnType('referral_codes', 'state_value', 'varchar', 3);
Database::changeColumnType('referral_codes', 'warning_message', 'varchar', 255);
Database::changeColumnType('referral_codes', 'warning_message', 'varchar', 255);
Database::changeColumnType('referral_codes', 'start_year', 'int', '', true);
Database::changeColumnType('referral_codes', 'start_year', 'int', '', true);

Database::changeColumnType('referral_actions', 'local_code', 'varchar', 20);
Database::changeColumnType('referral_actions', 'profile_exemptions', 'text');
Database::changeColumnType('referral_actions', 'profiles_edit', 'text');
Database::changeColumnType('referral_actions', 'profiles_view', 'text');
Database::changeColumnType('referral_actions', 'school_exemptions', 'text');
Database::changeColumnType('referral_actions', 'state_code', 'varchar', 20);
Database::changeColumnType('referral_actions', 'title', 'varchar', 255);
Database::changeColumnType('referral_actions', 'warning_message', 'varchar', 255);
Database::changeColumnType('referral_actions', 'start_year', 'int', '', true);
Database::changeColumnType('referral_actions', 'end_year', 'int', '', true);

if(!Database::columnExists('grade_posting_schemes', 'blank_exam_score')) {
	Database::createColumn('grade_posting_schemes', 'blank_exam_score', 'varchar', 10);
}

Database::query("
	update custom_field_log_entries set legacy_field_id = (select legacy_id from custom_fields where custom_fields.id=custom_field_log_entries.field_id) where legacy_field_id is null
");

Database::changeColumnType('student_report_card_grades', 'internal_notes', 'text');
