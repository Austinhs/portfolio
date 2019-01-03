<?php
if (!Database::tableExists('ps_fees')) {

	$database_type = Database::$type;
	$database_type = strtolower($database_type);

	$checks = array(
		"course_fees" => array(
		    'item_number',
		    'deleted',
		    'ten_ninety_eight'
		),
		"course_fee_groups" => array(
		    'deleted',
		    'item_number'
		),
		"course_fee_groups_joins_courses" => array(
		    'deleted'
		)
	);

	foreach($checks as $table => $columns) {
		foreach($columns as $column) {
			if(!Database::columnExists($table, $column)) {
				if(in_array($column, array('deleted', 'ten_ninety_eight'))) {
					$type = 'bigint';
				} else {
					$type = 'varchar(255)';
				}
				Database::createColumn($table, $column, $type);
			}
		}
	}

	$ps_fee_history_id_column = $database_type === 'mssql'
		? 'ID BIGINT default next value for ps_fees_seq'
		: "ID BIGINT default nextval('ps_fees_seq')";

	$new_limited_fees = "
		CREATE TABLE ps_fee_history (
		    {$ps_fee_history_id_column},
		    STUDENT_ID BIGINT,
		    FEE_ID BIGINT,
				PROGRAM_NUMBER BIGINT,
				SCHOOL_ID BIGINT,
				UNIQUE_FEE_ID BIGINT,
				PERIOD_ID VARCHAR(64),
		    SYEAR BIGINT
		)
	";

	$templates_sql = "
		SELECT
			CAST(NULL AS BIGINT) as id,
			CAST(id as BIGINT) as group_id,
			CAST(syear as BIGINT) as syear,
			CAST(school_id as BIGINT) as school_id,
			CAST(deleted as BIGINT) as deleted,
			title
		FROM
			course_fee_groups cfg
		WHERE
			cfg.parent_id IS NULL
			AND EXISTS (select '' from course_fee_groups cfg2 where cfg2.parent_id = cfg.id)
			AND cfg.deleted IS NULL
	";

	$template_update_sql = "
		UPDATE
			tmp_ps_fee_groups
		SET
			template_id = tmp.id
		FROM
			tmp_ps_fee_templates tmp
		WHERE
			tmp_ps_fee_groups.id = tmp.group_id
	";

	$middle_man_groups_sql = "
		SELECT
			cfg.ID,
			cfg.PARENT_ID
		FROM
			course_fee_groups cfg
		WHERE
			cfg.parent_id is NOT NULL
			AND cfg.parent_id != cfg.id
			AND cfg.deleted IS NULL
	";

	$real_groups_sql = "
		SELECT
			CAST(id as bigint) as id,
			CAST(school_id as bigint) as school_id,
			CAST(syear as bigint) as syear,
			1 as template,
			course_id,
			subject_id,
			CAST(NULL AS BIGINT) as template_id,
			CAST(deleted as BIGINT) as deleted
		FROM
			course_fee_groups cfg
		WHERE
			cfg.parent_id IS NULL
			AND EXISTS (select '' from course_fee_groups cfg2 where cfg2.parent_id = cfg.id)
		UNION ALL
		SELECT
			CAST(id as bigint),
			CAST(school_id as bigint) as school_id,
			CAST(syear as bigint) as syear,
			CAST(NULL as BIGINT) as template,
			course_id,
			subject_id,
			CAST(NULL as bigint) as template_id,
			CAST(deleted as BIGINT) as deleted
		FROM
			course_fee_groups cfg
		WHERE
			(course_id is not null or subject_id is not null)
	";

	if($database_type === 'mssql') {
		$course_fees_sql = "
			SELECT
				coalesce(cf.id, cf2.id) as id,
				coalesce(cf.group_id, cf2.group_id) as parent_id,
				cfg.item_number,
				CAST(cfg.syear as bigint) as syear,
				CAST(cfg.flat_fee as int) as flat_fee,
				CAST(cfg.tuition as int) as tuition,
				cfg.title,
				CAST(NULL AS VARCHAR(255)) as limited_period_id,
				CAST(NULL AS BIGINT) as program_limited,
				CAST(NULL AS BIGINT) as annual_fee,
				CAST(NULL AS BIGINT) as unique_fee_id,
				CAST(NULL AS BIGINT) as rollover_id,
				CAST(NULL AS BIGINT) as school_id,
				CAST(NULL AS BIGINT) as taxable,
				CAST(COALESCE(cf.amount, 0) as numeric(28, 10)) as resident_amount,
				CAST(COALESCE(cf2.amount, 0) as numeric(28, 10)) as non_resident_amount,
				COALESCE(cf.ten_ninety_eight, cf2.ten_ninety_eight, 0) as ten_ninety_eight,
				COALESCE(cf.accounting_strip_id, 0) as resident_accounting_strip_id,
				COALESCE(cf2.accounting_strip_id, 0) as non_resident_accounting_strip_id,
				CAST(NULL AS VARCHAR(255)) as resident_accounting_strip_hash,
				CAST(NULL AS VARCHAR(255)) as non_resident_accounting_strip_hash
			FROM
				course_fee_groups cfg
				LEFT JOIN course_fees cf ON cf.group_id = cfg.id and (cf.resident = 1) AND cf.deleted IS NULL
				LEFT JOIN course_fees cf2 on cf2.group_id = cfg.id and (cf2.resident = 0 OR cf2.resident IS NULL) and cf2.deleted IS NULL
			WHERE
				cfg.parent_id != cfg.id
				AND cfg.parent_id IS NOT NULL
				AND cfg.deleted IS NULL
		UNION ALL
			SELECT
				coalesce(cf.id, cf2.id) as id,
				coalesce(cf.group_id, cf2.group_id) as parent_id,
				cfg.item_number,
				CAST(cfg.syear as bigint) as syear,
				CAST(cfg.flat_fee as int) as flat_fee,
				CAST(cfg.tuition as int) as tuition,
				cfg.title,
				CAST(NULL AS VARCHAR(255)) as limited_period_id,
				CAST(NULL AS BIGINT) as program_limited,
				CAST(NULL AS BIGINT) as annual_fee,
				CAST(NULL AS BIGINT) as unique_fee_id,
				CAST(NULL AS BIGINT) as rollover_id,
				CAST(NULL AS BIGINT) as school_id,
				CAST(NULL AS BIGINT) as taxable,
				CAST(COALESCE(cf.amount, 0) as numeric(28, 10)) as resident_amount,
				CAST(COALESCE(cf2.amount, 0) as numeric(28, 10)) as non_resident_amount,
				COALESCE(cf.ten_ninety_eight, cf2.ten_ninety_eight, 0) as ten_ninety_eight,
				COALESCE(cf.accounting_strip_id, 0) as resident_accounting_strip_id,
				COALESCE(cf2.accounting_strip_id, 0) as non_resident_accounting_strip_id,
				CAST(NULL AS VARCHAR(255)) as resident_accounting_strip_hash,
				CAST(NULL AS VARCHAR(255)) as non_resident_accounting_strip_hash
			FROM
				course_fee_groups cfg
				LEFT JOIN course_fees cf ON cf.group_id = cfg.id AND (cf.resident = 1) AND cf.deleted IS NULL
				LEFT JOIN course_fees cf2 on cf2.group_id = cfg.id and (cf2.resident = 0 OR cf2.resident IS NULL) and cf2.deleted IS NULL
			WHERE
				cfg.parent_id IS NULL AND
				COALESCE(cfg.deleted, 0) = 0 AND
				NOT EXISTS (select '' from course_fee_groups cfg2 where cfg2.parent_id = cfg.id)
		";
	} else {
		$course_fees_sql = "
			SELECT
				coalesce(cf.id, cf2.id) as id,
				coalesce(cf.group_id, cf2.group_id) as parent_id,
				cfg.item_number,
				CAST(cfg.syear as bigint) as syear,
				CAST(cfg.flat_fee as int) as flat_fee,
				CAST(cfg.tuition as int) as tuition,
				cfg.title,
				CAST(NULL AS VARCHAR(255)) as limited_period_id,
				CAST(NULL AS BIGINT) as program_limited,
				CAST(NULL AS BIGINT) as annual_fee,
				CAST(NULL AS BIGINT) as unique_fee_id,
				CAST(NULL AS BIGINT) as rollover_id,
				CAST(NULL AS BIGINT) as school_id,
				CAST(NULL AS BIGINT) as taxable,
				CAST(COALESCE(cf.amount, 0) as numeric(28, 10)) as resident_amount,
				CAST(COALESCE(cf2.amount, 0) as numeric(28, 10)) as non_resident_amount,
				COALESCE(cf.ten_ninety_eight, cf2.ten_ninety_eight, 0) as ten_ninety_eight,
				COALESCE(cf.accounting_strip_id, 0) as resident_accounting_strip_id,
				COALESCE(cf2.accounting_strip_id, 0) as non_resident_accounting_strip_id,
				CAST(NULL AS VARCHAR(255)) as resident_accounting_strip_hash,
				CAST(NULL AS VARCHAR(255)) as non_resident_accounting_strip_hash
			FROM
				course_fee_groups cfg
				LEFT JOIN course_fees cf ON cf.group_id = cfg.id and (cf.resident = TRUE) AND cf.deleted IS NULL
				LEFT JOIN course_fees cf2 on cf2.group_id = cfg.id and (cf2.resident = FALSE OR cf2.resident IS NULL) and cf2.deleted IS NULL
			WHERE
				cfg.parent_id != cfg.id
				AND cfg.parent_id IS NOT NULL
				AND cfg.deleted IS NULL
		UNION ALL
			SELECT
				coalesce(cf.id, cf2.id) as id,
				coalesce(cf.group_id, cf2.group_id) as parent_id,
				cfg.item_number,
				CAST(cfg.syear as bigint) as syear,
				CAST(cfg.flat_fee as int) as flat_fee,
				CAST(cfg.tuition as int) as tuition,
				cfg.title,
				CAST(NULL AS VARCHAR(255)) as limited_period_id,
				CAST(NULL AS BIGINT) as program_limited,
				CAST(NULL AS BIGINT) as annual_fee,
				CAST(NULL AS BIGINT) as unique_fee_id,
				CAST(NULL AS BIGINT) as rollover_id,
				CAST(NULL AS BIGINT) as school_id,
				CAST(NULL AS BIGINT) as taxable,
				CAST(COALESCE(cf.amount, 0) as numeric(28, 10)) as resident_amount,
				CAST(COALESCE(cf2.amount, 0) as numeric(28, 10)) as non_resident_amount,
				COALESCE(cf.ten_ninety_eight, cf2.ten_ninety_eight, 0) as ten_ninety_eight,
				COALESCE(cf.accounting_strip_id, 0) as resident_accounting_strip_id,
				COALESCE(cf2.accounting_strip_id, 0) as non_resident_accounting_strip_id,
				CAST(NULL AS VARCHAR(255)) as resident_accounting_strip_hash,
				CAST(NULL AS VARCHAR(255)) as non_resident_accounting_strip_hash
			FROM
				course_fee_groups cfg
				LEFT JOIN course_fees cf ON cf.group_id = cfg.id AND (cf.resident = TRUE) AND cf.deleted IS NULL
				LEFT JOIN course_fees cf2 on cf2.group_id = cfg.id and (cf2.resident = FALSE OR cf2.resident IS NULL) and cf2.deleted IS NULL
			WHERE
				cfg.parent_id IS NULL AND
				COALESCE(cfg.deleted, 0) = 0 AND
				NOT EXISTS (select '' from course_fee_groups cfg2 where cfg2.parent_id = cfg.id)
		";
	}
	$joins_sql = "
		SELECT
			CAST(j.id as bigint) as id,
			CAST(j.syear as bigint) as syear,
			CAST(j.school_id as bigint) as school_id,
			CAST(j.subject_id as bigint) as subject_id,
			CAST(j.course_id as bigint) as course_id,
			tmp.id as template_id
		FROM
			course_fee_groups_joins_courses j JOIN
			tmp_ps_fee_templates tmp on j.group_id = tmp.group_id
	";

	$max_id_sql = "
		with tmp as(SELECT
		        MAX(cfg.id) AS max
		FROM
		        course_fee_groups cfg
		UNION ALL
		SELECT
		        MAX(cf.id) AS max
		FROM
		        course_fees cf
		UNION ALL
		SELECT
		        MAX(cfgjc.id) AS max  from course_fee_groups_joins_courses cfgjc
		)
		select max(max) as max
		from tmp;
	";

	$middle_man_groups = Database::get($middle_man_groups_sql);
	$max_id            = Database::get($max_id_sql);
	$max_id            = reset($max_id);
	$max_id            = intval($max_id['MAX']) + 1;
	$sequence          = "CREATE SEQUENCE ps_fees_seq START WITH {$max_id}";

	if($database_type === 'mssql') {
		$template_id = "ALTER TABLE tmp_ps_fee_templates add default next value for ps_fees_seq for ID";
		$course_id   = "ALTER TABLE tmp_ps_fees add default next value for ps_fees_seq for ID";
		$group_id    = "ALTER TABLE tmp_ps_fee_groups add default next value for ps_fees_seq for ID";
		$join_id     = "ALTER TABLE tmp_ps_fee_templates_joins add default next value for ps_fees_seq for ID";

		$creation = array(
			"SELECT * INTO tmp_ps_fees from ({$course_fees_sql}) tmp",
			"SELECT * INTO tmp_ps_fee_groups from ({$real_groups_sql}) tmp",
			"SELECT * INTO tmp_ps_fee_templates from ({$templates_sql}) tmp",
			"{$sequence}",
			"{$template_id}",
			"UPDATE tmp_ps_fee_templates set id = default",
			"{$course_id}",
			"{$group_id}",
			"{$template_update_sql}",
			"SELECT * INTO tmp_ps_fee_templates_joins from ({$joins_sql}) tmp",
			"CREATE SEQUENCE ps_fees_unique_seq",
			"{$new_limited_fees}"
		);
	} else {
		$template_id = "ALTER TABLE tmp_ps_fee_templates ALTER COLUMN id set default nextval('ps_fees_seq')";
		$course_id   = "ALTER TABLE tmp_ps_fees ALTER COLUMN id set default nextval('ps_fees_seq')";
		$group_id    = "ALTER TABLE tmp_ps_fee_groups ALTER COLUMN id set default nextval('ps_fees_seq')";
		$join_id     = "ALTER TABLE tmp_ps_fee_templates_joins ALTER COLUMN id set default nextval('ps_fees_seq')";

		$creation = array(
			"CREATE TABLE tmp_ps_fees AS ({$course_fees_sql})",
			"CREATE TABLE tmp_ps_fee_groups as ({$real_groups_sql})",
			"CREATE TABLE tmp_ps_fee_templates as ({$templates_sql})",
			"{$sequence}",
			"{$template_id}",
			"UPDATE tmp_ps_fee_templates set id = default",
			"{$course_id}",
			"{$group_id}",
			"{$template_update_sql}",
			"CREATE TABLE tmp_ps_fee_templates_joins as ({$joins_sql})",
			"CREATE SEQUENCE ps_fees_unique_seq",
			"{$new_limited_fees}"
		);
	}



	foreach($creation as $table_creation) {
		Database::query($table_creation);
	}


	foreach ($middle_man_groups as $middle_man_group) {
		$middle_man_id = $middle_man_group['ID'];
		$real_group_id = $middle_man_group['PARENT_ID'];

		$update_query = "UPDATE tmp_ps_fees set parent_id = {$real_group_id} where parent_id = {$middle_man_id}";
		Database::query($update_query);
	}
	if($database_type === 'mssql') {
		$clean_up = array(
			"SELECT * INTO ps_fees from tmp_ps_fees",
			"SELECT * INTO ps_fee_groups FROM tmp_ps_fee_groups",
			"SELECT * INTO ps_fee_templates FROM tmp_ps_fee_templates",
			"SELECT * INTO ps_fee_templates_joins FROM tmp_ps_fee_templates_joins",
			"ALTER TABLE ps_fee_templates ADD default next value for ps_fees_seq for ID",
			"ALTER TABLE ps_fees ADD default next value for ps_fees_seq for ID",
			"ALTER TABLE ps_fee_groups ADD default next value for ps_fees_seq for ID",
			"ALTER TABLE ps_fee_templates_joins ADD default next value for ps_fees_seq for ID",
			"ALTER TABLE ps_fees ADD default next value for ps_fees_unique_seq for unique_fee_id"
		);
	} else {
		$clean_up = array(
			"CREATE TABLE ps_fees as select * from tmp_ps_fees",
			"CREATE TABLE ps_fee_groups AS SELECT * FROM tmp_ps_fee_groups",
			"CREATE TABLE ps_fee_templates AS SELECT * FROM tmp_ps_fee_templates",
			"CREATE TABLE ps_fee_templates_joins as SELECT * FROM tmp_ps_fee_templates_joins",
			"ALTER TABLE ps_fee_templates ALTER COLUMN id set default nextval('ps_fees_seq')",
			"ALTER TABLE ps_fees ALTER COLUMN id set default nextval('ps_fees_seq')",
			"ALTER TABLE ps_fee_groups ALTER COLUMN id set default nextval('ps_fees_seq')",
			"ALTER TABLE ps_fee_templates_joins ALTER COLUMN id set default nextval('ps_fees_seq')",
			"ALTER TABLE ps_fees ALTER COLUMN unique_fee_id set default nextval('ps_fees_unique_seq')"
		);
	}
	foreach($clean_up as $things) {
		Database::query($things);
	}
} else {
	return false;
}
