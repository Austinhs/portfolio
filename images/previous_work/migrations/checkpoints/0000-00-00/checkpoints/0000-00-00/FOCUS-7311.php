<?php

Migrations::depend('FOCUS-6398');
Migrations::depend('FOCUS-7239');

if(purchasedCTE() && $GLOBALS['FocusFinanceConfig']['enabled']) {
	$true_val  = 'true';
	$false_val = 'false';

	if(Database::$type == 'mssql') {
		$true_val  = '1';
		$false_val = '0';
	}

	$main_query = "
		INSERT INTO ps_fees (
			id, item_number, syear, flat_fee, tuition,
			ten_ninety_eight, title, resident_amount,
			non_resident_amount, non_resident_accounting_strip_id,
			resident_accounting_strip_id
		)
		SELECT
			cf.id,
			cfg.item_number,
			CAST(cfg.syear AS bigint) AS syear,
			CAST(cfg.flat_fee AS int) AS flat_fee,
			CAST(cfg.tuition AS int) AS tuition,
			CAST(cf.ten_ninety_eight AS int) AS ten_ninety_eight,
			cfg.title,
			(
				CASE WHEN CF.RESIDENT = {$true_val} THEN CAST(
					COALESCE(cf.amount, 0) AS numeric(28, 10)
				) ELSE NULL END
			) AS resident_amount,
			(
				CASE WHEN CF.RESIDENT = {$false_val} OR CF.RESIDENT IS NULL THEN CAST(
					COALESCE(cf.amount, 0) AS numeric(28, 10)
				) ELSE NULL END
			) AS non_resident_amount,
			(
				CASE WHEN CF.RESIDENT = {$false_val} OR CF.RESIDENT IS NULL THEN cf.accounting_strip_id ELSE NULL END
			) AS non_resident_accounting_strip_id,
			(
				CASE WHEN CF.RESIDENT = {$true_val} THEN cf.accounting_strip_id ELSE NULL END
			) AS resident_accounting_strip_id
		FROM
			course_fees cf
			JOIN (
				SELECT
					source_id
				FROM
					gl_pos_invoice_allocation ia
				WHERE
					NOT EXISTS (
						SELECT
							''
						FROM
							ps_fees f
						WHERE
							f.id = ia.source_id
					)
					AND source_id IS NOT NULL
					AND ia.deleted IS NULL
					AND ia.cancelled_date IS NULL
			) aws ON cf.id = aws.source_id
			JOIN course_fee_groups cfg ON cfg.id = cf.group_id
	";

	$one_time_query = "
		INSERT INTO ps_fees (
			id, item_number, syear,
			ten_ninety_eight, title, resident_amount,
			non_resident_amount, non_resident_accounting_strip_id,
			resident_accounting_strip_id
		)
		SELECT
			DISTINCT cf.id,
			cfg.item_number,
			CAST(cfg.syear AS bigint) AS syear,
			CAST(cf.ten_ninety_eight AS int) AS ten_ninety_eight,
			cfg.title,
			(
				CASE WHEN CFG.RESIDENT = {$true_val} THEN CAST(
					COALESCE(cf.amount, 0) AS numeric(28, 10)
				) ELSE NULL END
			) AS resident_amount,
			(
				CASE WHEN CF.RESIDENT = {$false_val} OR CF.RESIDENT IS NULL THEN CAST(
					COALESCE(cf.amount, 0) AS numeric(28, 10)
				) ELSE NULL END
			) AS non_resident_amount,
			(
				CASE WHEN CF.RESIDENT = {$false_val} OR CF.RESIDENT IS NULL THEN cf.accounting_strip_id ELSE NULL END
			) AS non_resident_accounting_strip_id,
			(
				CASE WHEN CF.RESIDENT = {$true_val} THEN cf.accounting_strip_id ELSE NULL END
			) AS resident_accounting_strip_id
		FROM
			ps_fees_one_time cf
			JOIN (
				SELECT
					source_id
				FROM
					gl_pos_invoice_allocation ia
				WHERE
					NOT EXISTS (
						SELECT
							''
						FROM
							ps_fees f
						WHERE
							f.id = ia.source_id
					)
					AND source_id IS NOT NULL
					AND ia.deleted IS NULL
					AND ia.cancelled_date IS NULL
			) aws ON cf.parent_id = aws.source_id
			JOIN ps_fees_one_time cfg ON cfg.id = cf.parent_id
	";

	Database::query($main_query);
	Database::query($one_time_query);

	$tables_to_modify = array(
		"ps_fee_groups",
		"ps_fee_history",
		"ps_fee_templates",
		"ps_fee_templates_joins",
		"ps_fees"
	);

	foreach($tables_to_modify as $table){
		if(!Database::columnExists($table, 'deleted')) {
			Database::createColumn($table, 'deleted', 'bigint');
		}
	}

	$deleted_query = "
		SELECT
			*
		FROM
			DATABASE_OBJECT_LOG
		WHERE
			record_class = 'PsFees' and
			action = 'DELETE' and
			query not like '%course_fees%'
	";

	$deleted_records = Database::get($deleted_query);

	if(!empty($deleted_records)) {
		foreach($deleted_records as $record) {
			$record_details = json_decode($record['BEFORE'], true);

			$columns = array(
				'DELETED'
			);
			$values = array(
				'1'
			);

			foreach($record_details as $column => $value) {
				if(empty($value)) {
					continue;
				}

				$columns[] = $column;
				$values[]  = $value;
			}

			$columns = join(',', $columns);
			$values  = join("','", $values);

			$insert_query = "
				INSERT INTO
					PS_FEES
				({$columns})
					VALUES
				('{$values}')";

				Database::query($insert_query);
		}
	}
} else {
	return false;
}
